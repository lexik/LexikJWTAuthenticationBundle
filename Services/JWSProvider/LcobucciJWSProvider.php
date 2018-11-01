<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
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
     * @var RawKeyLoader
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
     * @param RawKeyLoader $keyLoader
     * @param string       $cryptoEngine
     * @param string       $signatureAlgorithm
     * @param int|null     $ttl
     * @param int          $clockSkew
     *
     * @throws \InvalidArgumentException If the given crypto engine is not supported
     */
    public function __construct(RawKeyLoader $keyLoader, $cryptoEngine, $signatureAlgorithm, $ttl, $clockSkew)
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
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload, array $header = [])
    {
        $jws = new Builder();
        foreach ($header as $k => $v) {
            $jws->setHeader($k, $v);
        }
        $jws->setIssuedAt(time());

        if (null !== $this->ttl && !isset($payload['exp'])) {
            $jws->setExpiration(time() + $this->ttl);
        }

        foreach ($payload as $name => $value) {
            $jws->set($name, $value);
        }

        $e = null;

        try {
            $this->sign($jws);
        } catch (\InvalidArgumentException $e) {
        }

        return new CreatedJWS((string) $jws->getToken(), null === $e);
    }

    /**
     * {@inheritdoc}
     */
    public function load($token)
    {
        $jws = (new Parser())->parse((string) $token);

        $payload = [];
        foreach ($jws->getClaims() as $claim) {
            $payload[$claim->getName()] = $claim->getValue();
        }

        return new LoadedJWS($payload, $this->verify($jws), null !== $this->ttl, $jws->getHeaders(), $this->clockSkew);
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

    private function sign(Builder $jws)
    {
        if ($this->signer instanceof Hmac) {
            return $jws->sign($this->signer, $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE));
        }

        return $jws->sign(
            $this->signer,
            new Key($this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE), $this->keyLoader->getPassphrase())
        );
    }

    private function verify(Token $jwt)
    {
        if (!$jwt->validate(new ValidationData(time() + $this->clockSkew))) {
            return false;
        }

        if ($this->signer instanceof Hmac) {
            return $jwt->verify($this->signer, $this->keyLoader->loadKey(RawKeyLoader::TYPE_PRIVATE));
        }

        return $jwt->verify($this->signer, $this->keyLoader->loadKey(RawKeyLoader::TYPE_PUBLIC));
    }
}
