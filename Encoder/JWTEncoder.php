<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use InvalidArgumentException;
use Namshi\JOSE\SimpleJWS;

/**
 * JWTEncoder
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class JWTEncoder implements JWTEncoderInterface
{
    const ALGORYTHM = 'RS256';

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
     * @param string $privateKey
     * @param string $publicKey
     * @param string $passPhrase
     */
    public function __construct($privateKey, $publicKey, $passPhrase)
    {
        $this->privateKey = $privateKey;
        $this->publicKey  = $publicKey;
        $this->passPhrase = $passPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        $jws = new SimpleJWS(['alg' => self::ALGORYTHM]);
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
            /** @var SimpleJWS $jws */
            $jws = SimpleJWS::load($token);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        if (!$jws->isValid($this->getPublicKey(), self::ALGORYTHM)) {
            return false;
        }

        return $jws->getPayload();
    }

    /**
     * @return bool|resource
     */
    protected function getPrivateKey()
    {
        $key = openssl_pkey_get_private('file://' . $this->privateKey, $this->passPhrase);

        if(!$key) {
            throw new \RuntimeException('The public key cannot be loaded, have you set the %lexik_jwt_authentication.public_key_path% parameter ?');
        }

        return $key;
    }

    /**
     * @return resource
     */
    protected function getPublicKey()
    {
        $key = openssl_pkey_get_public('file://' . $this->publicKey);

        if(!$key) {
            throw new \RuntimeException('The public key cannot be loaded, have you set the %lexik_jwt_authentication.public_key_path% parameter ?');
        }

        return $key;
    }
}
