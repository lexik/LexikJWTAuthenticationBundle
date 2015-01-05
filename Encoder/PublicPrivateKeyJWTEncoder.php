<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Namshi\JOSE\JWS;

/**
 * JWTEncoder
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class PublicPrivateKeyJWTEncoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    protected $privateKey;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $passPhrase;

    /**
     * @param string $algorithm
     * @param string $privateKey
     * @param string $publicKey
     * @param string $passPhrase
     */
    public function __construct($algorithm, $privateKey, $publicKey, $passPhrase)
    {
        $this->algorithm  = $algorithm;
        $this->privateKey = $privateKey;
        $this->publicKey  = $publicKey;
        $this->passPhrase = $passPhrase;

        $this->checkOpenSSLConfig();
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        $jws = new JWS($this->algorithm);
        $jws->setPayload($data);
        $jws->sign($this->getPrivateKey());

        return $jws->getTokenString();
    }

    /**
     * {@inheritdoc}
     */
    public function decode($token)
    {
        try {
            $jws = JWS::load($token);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        if (!$jws->isValid($this->getPublicKey())) {
            return false;
        }

        return $jws->getPayload();
    }

    /**
     * Checks that configured keys exists and private key can be parsed using the passphrase
     *
     * @throws \RuntimeException
     */
    public function checkOpenSSLConfig()
    {
        if (!file_exists($this->privateKey)) {
            throw new \RuntimeException(sprintf(
                'Private key "%s" doesn\'t exist.',
                $this->privateKey
            ));
        }

        if (!file_exists($this->publicKey)) {
            throw new \RuntimeException(sprintf(
                'Public key "%s" doesn\'t exist.',
                $this->publicKey
            ));
        }

        if (!openssl_pkey_get_private('file://' . $this->privateKey, $this->passPhrase)) {
            throw new \RuntimeException(sprintf(
                'Failed to open private key "%s". Did you correctly configure the corresponding passphrase?',
                $this->privateKey
            ));
        }
    }

    /**
     * @return bool|resource
     */
    protected function getPrivateKey()
    {
        return openssl_pkey_get_private('file://' . $this->privateKey, $this->passPhrase);
    }

    /**
     * @return resource
     */
    protected function getPublicKey()
    {
        return openssl_pkey_get_public('file://' . $this->publicKey);
    }
}
