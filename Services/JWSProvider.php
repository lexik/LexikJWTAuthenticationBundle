<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

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
class JWSProvider implements JWSProviderInterface
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
    private $encryptionAlgorithm;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string             $encryptionEngine
     * @param string             $encryptionAlgorithm
     *
     * @throws \InvalidArgumentException If the given algorithm is not supported
     */
    public function __construct(KeyLoaderInterface $keyLoader, $encryptionEngine, $encryptionAlgorithm)
    {
        $encryptionEngine = $encryptionEngine == 'openssl' ? 'OpenSSL' : 'SecLib';

        if (!$this->isAlgorithmSupportedForEngine($encryptionEngine, $encryptionAlgorithm)) {
            throw new \InvalidArgumentException(
                sprintf('The algorithm "%s" is not supported for %s', $encryptionAlgorithm, $encryptionEngine)
            );
        }

        $this->keyLoader           = $keyLoader;
        $this->encryptionEngine    = $encryptionEngine;
        $this->encryptionAlgorithm = $encryptionAlgorithm;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $payload)
    {
        $jws = new JWS(['alg' => $this->encryptionAlgorithm], $this->encryptionEngine);

        $jws->setPayload($payload);
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
            $jws->verify($this->keyLoader->loadKey('public'), $this->encryptionAlgorithm)
        );
    }

    /**
     * @param string $encryptionEngine
     * @param string $encryptionAlgorithm
     *
     * @return bool
     */
    private function isAlgorithmSupportedForEngine($encryptionEngine, $encryptionAlgorithm)
    {
        $signerClass = sprintf('Namshi\\JOSE\\Signer\\%s\\%s', $encryptionEngine, $encryptionAlgorithm);

        return class_exists($signerClass);
    }
}
