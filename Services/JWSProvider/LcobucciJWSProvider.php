<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
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
     * @var int
     */
    private $ttl;

    /**
     * @var int
     */
    private $clockSkew;

    /**
     * @var bool
     */
    private $legacyJWTApi;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string             $cryptoEngine
     * @param string             $signatureAlgorithm
     * @param int|null           $ttl
     * @param int                $clockSkew
     *
     * @throws \InvalidArgumentException If the given crypto engine is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, $cryptoEngine, $signatureAlgorithm, $ttl, $clockSkew)
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
        $this->signer    = $this->getSignerForAlgorithm($signatureAlgorithm);
        $this->ttl       = $ttl;
        $this->clockSkew = $clockSkew;
        $this->legacyJWTApi = !method_exists(Builder::class, 'with');
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload, array $header = [])
    {
        $jws = new Builder();

        foreach ($header as $k => $v) {
            $jws->{$this->legacyJWTApi ? 'setHeader' : 'withHeader'}($k, $v);
        }

        $now = time();

        if ($this->legacyJWTApi) {
            $jws->setIssuedAt($now);
        } else {
            $jws->issuedAt(new \DateTimeImmutable("@{$now}"));
        }

        if (null !== $this->ttl || isset($payload['exp'])) {
            $exp = isset($payload['exp']) ? $payload['exp'] : $now + $this->ttl;
            unset($payload['exp']);

            if ($this->legacyJWTApi) {
                $jws->setExpiration($exp);
            } else {
                $jws->expiresAt($exp instanceof \DateTimeImmutable ? $exp : new \DateTimeImmutable("@$exp"));
            }
        }

        if (isset($payload['sub'])) {
            $jws->{$this->legacyJWTApi ? 'setSubject' : 'relatedTo'}($payload['sub']);
            unset($payload['sub']);
        }

        foreach ($payload as $name => $value) {
            $jws->{$this->legacyJWTApi ? 'set' : 'with'}($name, $value);
        }

        $e = null;

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
        $jws = (new Parser())->parse((string) $token);
        $payload = [];

        if ($this->legacyJWTApi) {
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
            $this->legacyJWTApi ? $jws->getHeaders() : $jws->headers()->all(),
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
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, __CLASS__)
            );
        }

        $signerClass = $signerMap[$signatureAlgorithm];

        return new $signerClass();
    }

    private function getSignedToken(Builder $jws)
    {
        if ($this->legacyJWTApi) {
            $jws->sign(
                $this->signer,
                new Key($this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE), $this->signer instanceof Hmac ? '' : $this->keyLoader->getPassphrase())
            );

            return $jws->getToken();
        }

        return $jws->getToken(
            $this->signer,
            new Key($this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE), $this->signer instanceof Hmac ? '' : $this->keyLoader->getPassphrase())
        );
    }

    private function verify(Token $jwt)
    {
        if ($this->legacyJWTApi) {
            if (!$jwt->validate(new ValidationData(time() + $this->clockSkew))) {
                return false;
            }

            return $jwt->verify(
                $this->signer,
                $this->signer instanceof Hmac ? $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE) : $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC)
            );
        }

        $clock = SystemClock::fromSystemTimezone();
        $validator = new Validator();

        return $validator->validate(
            $jwt,
            new ValidAt($clock, new \DateInterval("PT{$this->clockSkew}S")),
            new SignedWith($this->signer, new Key($this->signer instanceof Hmac ? $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE) : $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC)))
        );
    }
}
