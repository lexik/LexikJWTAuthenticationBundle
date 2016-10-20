<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;
use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;

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
     * @param RawKeyLoader $keyLoader
     * @param string       $cryptoEngine
     * @param string       $signatureAlgorithm
     * @param int          $ttl
     *
     * @throws \InvalidArgumentException If the given algorithm|engine is not supported
     */
    public function __construct(RawKeyLoader $keyLoader, $cryptoEngine, $signatureAlgorithm, $ttl)
    {
        $this->keyLoader = $keyLoader;
        $this->signer    = $this->getSignerForAlgorithm($signatureAlgorithm);
        $this->ttl       = $ttl;

        if ('openssl' !== $cryptoEngine) {
            throw new \InvalidArgumentException(sprintf('The %s provider supports only "openssl" as crypto engine.', __CLASS__));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload)
    {
        $jws = (new Builder())
            ->setIssuedAt(time())
            ->setExpiration(time() + $this->ttl);

        foreach ($payload as $name => $value) {
            $jws->set($name, $value);
        }

        try {
            $jws->sign(
                $this->signer,
                new Key($this->keyLoader->loadKey('private'), $this->keyLoader->getPassphrase())
            );
            $signed = true;
        } catch (\InvalidArgumentException $e) {
            $signed = false;
        }

        return new CreatedJWS((string) $jws->getToken(), $signed);
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

        return new LoadedJWS(
            $payload,
            $jws->verify($this->signer, $this->keyLoader->loadKey('public')) && $jws->validate(new ValidationData())
        );
    }

    private function getSignerForAlgorithm($signatureAlgorithm)
    {
        if (0 === strpos($signatureAlgorithm, 'HS')) {
            $signerType = 'Hmac';
        } elseif (0 === strpos($signatureAlgorithm, 'RS')) {
            $signerType = 'Rsa';
        } elseif (0 === strpos($signatureAlgorithm, 'EC')) {
            $signerType = 'Ecdsa';
        }

        if (!isset($signerType)) {
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported by %s', $signatureAlgorithm, __CLASS__)
            );
        }

        $bits   = substr($signatureAlgorithm, 2, strlen($signatureAlgorithm));
        $signer = sprintf('Lcobucci\\JWT\\Signer\\%s\\Sha%s', $signerType, $bits);

        return new $signer();
    }
}
