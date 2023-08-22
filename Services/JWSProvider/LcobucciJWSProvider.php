<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Ecdsa;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder as JWTBuilder;
use Lcobucci\JWT\Token\Parser as JWTParser;
use Lcobucci\JWT\Token\RegisteredClaims;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Validator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

/**
 * @final
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class LcobucciJWSProvider implements JWSProviderInterface
{
    private KeyLoaderInterface $keyLoader;

    private Clock $clock;

    private Signer $signer;

    private ?int $ttl;

    private ?int $clockSkew;

    private bool $allowNoExpiration;

    /**
     * @throws \InvalidArgumentException If the given crypto engine is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, string $signatureAlgorithm, ?int $ttl, ?int $clockSkew, bool $allowNoExpiration = false, Clock $clock = null)
    {
        if (null === $clock) {
            $clock = new SystemClock(new \DateTimeZone('UTC'));
        }

        $this->keyLoader = $keyLoader;
        $this->clock = $clock;
        $this->signer = $this->getSignerForAlgorithm($signatureAlgorithm);
        $this->ttl = $ttl;
        $this->clockSkew = $clockSkew;
        $this->allowNoExpiration = $allowNoExpiration;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload, array $header = [])
    {
        if (class_exists(JWTBuilder::class)) {
            $jws = new JWTBuilder(new JoseEncoder(), ChainedFormatter::default());
        } else {
            $jws = new Builder();
        }

        foreach ($header as $k => $v) {
            $jws = $jws->withHeader($k, $v);
        }

        $now = time();

        $issuedAt = $payload['iat'] ?? $now;
        unset($payload['iat']);

        $jws->issuedAt(!$issuedAt instanceof \DateTimeImmutable ? new \DateTimeImmutable("@{$issuedAt}") : $issuedAt);

        if (null !== $this->ttl || isset($payload['exp'])) {
            $exp = $payload['exp'] ?? $now + $this->ttl;
            unset($payload['exp']);

            if ($exp) {
                $jws->expiresAt($exp instanceof \DateTimeImmutable ? $exp : (new \DateTimeImmutable("@$exp")));
            }
        }

        if (isset($payload['sub'])) {
            $jws = $jws->relatedTo($payload['sub']);
            unset($payload['sub']);
        }

        if (interface_exists(RegisteredClaims::class)) {
            $jws = $this->addStandardClaims($jws, $payload);
        }

        foreach ($payload as $name => $value) {
            $jws = $jws->withClaim($name, $value);
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
            $jws = (new JWTParser(new JoseEncoder()))->parse($token);
        } else {
            $jws = (new Parser())->parse($token);
        }

        $payload = [];
        foreach ($jws->claims()->all() as $name => $value) {
            if ($value instanceof \DateTimeInterface) {
                $value = $value->getTimestamp();
            }
            $payload[$name] = $value;
        }

        return new LoadedJWS(
            $payload,
            $this->verify($jws),
            false == $this->allowNoExpiration,
            $jws->headers()->all(),
            $this->clockSkew
        );
    }

    private function getSignerForAlgorithm($signatureAlgorithm): Signer
    {
        $signerMap = [
            'HS256' => Sha256::class,
            'HS384' => Sha384::class,
            'HS512' => Sha512::class,
            'RS256' => Signer\Rsa\Sha256::class,
            'RS384' => Signer\Rsa\Sha384::class,
            'RS512' => Signer\Rsa\Sha512::class,
            'ES256' => Signer\Ecdsa\Sha256::class,
            'ES384' => Signer\Ecdsa\Sha384::class,
            'ES512' => Signer\Ecdsa\Sha512::class,
        ];

        if (!isset($signerMap[$signatureAlgorithm])) {
            throw new \InvalidArgumentException(sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, self::class));
        }

        $signerClass = $signerMap[$signatureAlgorithm];

        if (is_subclass_of($signerClass, Ecdsa::class) && method_exists($signerClass, 'create')) {
            return $signerClass::create();
        }

        return new $signerClass();
    }

    private function getSignedToken(Builder $jws): string
    {
        $key = InMemory::plainText($this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PRIVATE), $this->signer instanceof Hmac ? '' : (string) $this->keyLoader->getPassphrase());

        $token = $jws->getToken($this->signer, $key);

        return $token->toString();
    }

    private function verify(Token $jwt): bool
    {
        $key = InMemory::plainText($this->signer instanceof Hmac ? $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PRIVATE) : $this->keyLoader->loadKey(KeyLoaderInterface::TYPE_PUBLIC));

        $validator = new Validator();
        $classValidator = class_exists(LooseValidAt::class) ? LooseValidAt::class : ValidAt::class;

        $isValid = $validator->validate(
            $jwt,
            new $classValidator($this->clock, new \DateInterval("PT{$this->clockSkew}S")),
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
                new $classValidator($this->clock, new \DateInterval("PT{$this->clockSkew}S")),
                new SignedWith($this->signer, InMemory::plainText($key))
            );

            if ($isValid) {
                return true;
            }
        }

        return false;
    }

    private function addStandardClaims(Builder $builder, array &$payload): void
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
                $builder = \call_user_func_array([$builder, $mutator], $value);
                continue;
            }

            $builder = $builder->{$mutator}($value);
        }

        return $builder;
    }
}
