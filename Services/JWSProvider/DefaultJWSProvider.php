<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\CreatedJWS;
use Lexik\Bundle\JWTAuthenticationBundle\Signature\LoadedJWS;
use Namshi\JOSE\JWS;

/**
 * JWS Provider, Namshi\JOSE library integration.
 * Supports OpenSSL and phpseclib encryption engines.
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class DefaultJWSProvider implements JWSProviderInterface
{
    /**
     * @var KeyLoaderInterface
     */
    private $keyLoader;

    /**
     * @var string
     */
    private $encryptionEngine;

    /**
     * @var string
     */
    private $signatureAlgorithm;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string             $encryptionEngine
     * @param string             $signatureAlgorithm
     *
     * @throws \InvalidArgumentException If the given algorithm is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, $encryptionEngine, $signatureAlgorithm)
    {
        $encryptionEngine = $encryptionEngine == 'openssl' ? 'OpenSSL' : 'SecLib';

        if (!$this->isAlgorithmSupportedForEngine($encryptionEngine, $signatureAlgorithm)) {
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported for %s', $signatureAlgorithm, $encryptionEngine)
            );
        }

        $this->keyLoader          = $keyLoader;
        $this->encryptionEngine   = $encryptionEngine;
        $this->signatureAlgorithm = $signatureAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload)
    {
        $jws = new JWS(['alg' => $this->signatureAlgorithm], $this->encryptionEngine);

        $jws->setPayload($payload + ['iat' => time()]);
        $jws->sign(
            $this->keyLoader->loadKey('private'),
            $this->keyLoader->getPassphrase()
        );

        return new CreatedJWS($jws->getTokenString(), $jws->isSigned());
    }

    /**
     * {@inheritdoc}
     */
    public function load($token)
    {
        $jws = JWS::load($token, false, null, $this->encryptionEngine);

        return new LoadedJWS(
            $jws->getPayload(),
            $jws->verify($this->keyLoader->loadKey('public'), $this->signatureAlgorithm)
        );
    }

    /**
     * @param string $encryptionEngine
     * @param string $signatureAlgorithm
     *
     * @return bool
     */
    private function isAlgorithmSupportedForEngine($encryptionEngine, $signatureAlgorithm)
    {
        $signerClass = sprintf('Namshi\\JOSE\\Signer\\%s\\%s', $encryptionEngine, $signatureAlgorithm);

        return class_exists($signerClass);
    }
}
