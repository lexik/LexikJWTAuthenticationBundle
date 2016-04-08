<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\KeyLoaderInterface;
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
    protected $keyLoader;

    /**
     * @var string
     */
    protected $encryptionEngine;

    /**
     * @var string
     */
    protected $encryptionAlgorithm;

    /**
     * @var JWS
     */
    protected $jws;

    /**
     * @var string
     */
    protected $status;

    /**
     * @param KeyLoaderInterface $keyLoader
     * @param string             $encryptionEngine
     * @param string             $encryptionAlgorithm
     *
     * @throws \InvalidArgumentException If the given algorithm is not supported.
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
    public function createSignedToken(array $payload)
    {
        $this->jws = new JWS(['alg' => $this->encryptionAlgorithm], $this->encryptionEngine);
        $this->jws->setPayload($payload);
        $this->sign();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function loadSignature($token)
    {
        $this->jws = JWS::load($token, false, null, $this->encryptionEngine);
        $this->verify();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        if (self::SIGNED !== $this->status) {
            return;
        }

        return $this->getJWS()->getTokenString();
    }

    /**
     * @return array $payload
     */
    public function getPayload()
    {
        return $this->getJWS()->getPayload();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @link \Namshi\JOSE\SimpleJWS::isExpired()
     *
     * @return bool
     */
    public function isExpired()
    {
        $payload = $this->getJWS()->getPayload();

        if (isset($payload['exp']) && is_numeric($payload['exp'])) {
            $now = new \DateTime('now');

            return ($now->format('U') - $payload['exp']) > 0;
        }

        return false;
    }

    /**
     * Signs the created JWS signature.
     */
    protected function sign()
    {
        $this->getJWS()->sign(
            $this->keyLoader->loadKey('private'),
            $this->keyLoader->getPassphrase()
        );

        if (true === $this->getJWS()->isSigned()) {
            $this->status = self::SIGNED;
        }
    }

    /**
     * Verifies the loaded JWS signature.
     */
    protected function verify()
    {
        $verified = $this->getJWS()->verify(
            $this->keyLoader->loadKey('public'),
            $this->encryptionAlgorithm
        );

        if (true === $verified) {
            $this->status = self::VERIFIED;
        }
    }

    /**
     * @return JWS
     *
     * @throws \LogicException If there is no JWS instance defined.
     */
    protected function getJWS()
    {
        if (null === $this->jws) {
            throw new \LogicException('There is no JWS instance defined. Did you forget to call %s::createSignedToken() or %s::loadSignature()?', get_class($this));
        }

        return $this->jws;
    }

    /**
     * @param string $encryptionEngine
     * @param string $encryptionAlgorithm
     *
     * @return bool
     */
    protected function isAlgorithmSupportedForEngine($encryptionEngine, $encryptionAlgorithm)
    {
        $signerClass = sprintf('Namshi\\JOSE\\Signer\\%s\\%s', $encryptionEngine, $encryptionAlgorithm);

        return class_exists($signerClass);
    }
}
