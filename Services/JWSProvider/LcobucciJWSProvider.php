<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Encoding\MicrosecondBasedDateConversion;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder as JWTBuilder;
use Lcobucci\JWT\Token\Parser as JWTParser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Validator;
use Lcobucci\JWT\ValidationData;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

/**
 * @final
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LcobucciJWSProvider implements JWSProviderInterface
{
    /**
     * @var KeyLoaderInterface
     */
    private $keyLoader;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var int|null
     */
    private $ttl;

    /**
     * @var int|null
     */
    private $clockSkew;

    /**
     * @var bool
     */
    private $useDateObjects;

    /**
     * @throws \InvalidArgumentException If the given crypto engine is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, string $cryptoEngine, string $signatureAlgorithm, ?int $ttl, ?int $clockSkew)
    {
        if ('openssl' !== $cryptoEngine) {
            throw new \InvalidArgumentException(sprintf('The %s provider supports only "openssl" as crypto engine.', __CLASS__));
        }

        if (null !== $ttl && !is_numeric($ttl)) {
            throw new \InvalidArgumentException(sprintf('The TTL should be a numeric value, got %s instead.', $ttl));
        }

        if (null !== $clockSkew && !is_numeric($clockSkew)) {
            throw new \InvalidArgumentException(sprintf('The clock skew should be a numeric value, got %s instead.', $clockSkew));
        }

        $this->keyLoader = $keyLoader;
        $this->signer = $this->getSignerForAlgorithm($signatureAlgorithm);
        $this->ttl = $ttl;
        $this->clockSkew = $clockSkew;
        $this->useDateObjects = method_exists(Token::class, 'payload') || class_exists(Plain::class);  // exists only on lcobucci/jwt 3.4+
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload, array $header = [])
    {
        if (class_exists(JWTBuilder::class)) {
            $jws = new JWTBuilder(new JoseEncoder(), new MicrosecondBasedDateConversion());
        } else {
            $jws = new Builder();
        }

        foreach ($header as $k => $v) {
            $jws->withHeader($k, $v);
        }

        $now = time();

        $issuedAt = isset($payload['iat']) ? $payload['iat'] : $now;
        unset($payload['iat']);

        $jws->issuedAt($this->useDateObjects && !$issuedAt instanceof \DateTimeImmutable ? new \DateTimeImmutable("@{$issuedAt}") : $issuedAt);

        if (null !== $this->ttl || isset($payload['exp'])) {
            $exp = isset($payload['exp']) ? $payload['exp'] : $now + $this->ttl;
            unset($payload['exp']);

            $jws->expiresAt($exp instanceof \DateTimeImmutable ? $exp : ($this->useDateObjects ? new \DateTimeImmutable("@$exp") : $exp));
        }

        if (isset($payload['sub'])) {
            $jws->relatedTo($payload['sub']);
            unset($payload['sub']);
        }

        if (interface_exists(RegisteredClaims::class)) {
            $this->addStandardClaims($jws, $payload);
        }

        foreach ($payload as $name => $value) {
            $jws->withClaim($name, $value);
        }

        $e = $token = null;
        try {
            $token = $this->getSignedToken($jws);
        } catch (\InvalidArgumentException $e) {
        }

        return new CreatedJWS((string) $token, null === $e);
    }

    /**
     * {@inheritdoc}
     */
    public function load($token)
    {
        if (class_exists(JWTParser::class)) {
            $jws = (new JWTParser(new JoseEncoder()))->parse((string) $token);
        } else {
            $jws = (new Parser())->parse((string) $token);
        }

        $payload = [];

        if (!$this->useDateObjects) {
            foreach ($jws->getClaims() as $claim) {
                $payload[$claim->getName()] = $claim->getValue();
            }
        } else {
            foreach ($jws->claims()->all() as $name => $value) {
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->getTimestamp();
                }
                $payload[$name] = $value;
            }
        }

        $jws = new LoadedJWS(
            $payload,
            $this->verify($jws),
            null !== $this->ttl,
            $this->useDateObjects ? $jws->headers()->all() : $jws->getHeaders(),
            $this->clockSkew
        );

        return $jws;
    }

    private function getSignerForAlgorithm($signatureAlgorithm)
    {
        $signerMap = [
            'HS256' => Signer\Hmac\Sha256::class,
            'HS384' => Signer\Hmac\Sha384::class,
            'HS512' => Signer\Hmac\Sha512::class,
            'RS256' => Signer\Rsa\Sha256::class,
            'RS384' => Signer\Rsa\Sha384::class,
            'RS512' => Signer\Rsa\Sha512::class,
            'EC256' => Signer\Ecdsa\Sha256::class,
            'EC384' => Signer\Ecdsa\Sha384::class,
            'EC512' => Signer\Ecdsa\Sha512::class,
        ];

        if (!isset($signerMap[$signatureAlgorithm])) {
            throw new \InvalidArgumentException(sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, __CLASS__));
        }

        $signerClass = $signerMap[$signatureAlgorithm];

        if (is_subclass_of($signerClass, Signer\Ecdsa::class) && method_exists($signerClass, 'create')) {
            return $signerClass::create();
        }

        return new $signerClass();
    }

    private function getSignedToken(Builder $jws)
    {
        if (class_exists(InMemory::class)) {
            $key = InMemory::plainText($this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE), $this->signer instanceof Hmac ? '' : (string) $this->keyLoader->getPassphrase());
        } else {
            $key = new Key($this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE), $this->signer instanceof Hmac ? '' : (string) $this->keyLoader->getPassphrase());
        }

        $token = $jws->getToken($this->signer, $key);

        if (!$token instanceof Plain) {
            return (string) $token;
        }

        return $token->toString();
    }

    private function verify(Token $jwt)
    {
        if (!$this->useDateObjects) {
            if (!$jwt->validate(new ValidationData(time() + $this->clockSkew))) {
                return false;
            }

            if ($this->signer instanceof Hmac) {
                return $jwt->verify(
                    $this->signer,
                    $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE)
                );
            }

            if (!empty($keys = $this->keyLoader->getAdditionalPublicKeys())) {
                foreach ($keys as $key) {
                    if ($jwt->verify($this->signer, $key)) {
                        return true;
                    }
                }

                return false;
            }
        }

        if (class_exists(InMemory::class)) {
            $key = InMemory::plainText($this->signer instanceof Hmac ? $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE) : $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC));
        } else {
            $key = new Key($this->signer instanceof Hmac ? $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE) : $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC));
        }

        $clock = SystemClock::fromUTC();
        $validator = new Validator();

        $isValid = $validator->validate(
            $jwt,
            new ValidAt($clock, new \DateInterval("PT{$this->clockSkew}S")),
            new SignedWith($this->signer, $key)
        );

        $publicKeys = $this->keyLoader->getAdditionalPublicKeys();
        if ($isValid || $this->signer instanceof Hmac || empty($publicKeys)) {
            return $isValid;
        }

        // If the key used to verify the token is invalid, and it's not Hmac algorithm, try with additional public keys
        foreach ($publicKeys as $key) {
            $isValid = $validator->validate(
                $jwt,
                new ValidAt($clock, new \DateInterval("PT{$this->clockSkew}S")),
                new SignedWith($this->signer, InMemory::plainText($key))
            );

            if ($isValid) {
                return true;
            }
        }

        return false;
    }

    private function addStandardClaims(Builder $builder, array &$payload)
    {
        $mutatorMap = [
            RegisteredClaims::AUDIENCE => 'permittedFor',
            RegisteredClaims::ID => 'identifiedBy',
            RegisteredClaims::ISSUER => 'issuedBy',
            RegisteredClaims::NOT_BEFORE => 'canOnlyBeUsedAfter',
        ];

        foreach ($payload as $claim => $value) {
            if (!isset($mutatorMap[$claim])) {
                continue;
            }

            $mutator = $mutatorMap[$claim];
            unset($payload[$claim]);

            if (\is_array($value)) {
                \call_user_func_array([$builder, $mutator], $value);
                continue;
            }

            $builder->{$mutator}($value);
        }
    }
}
