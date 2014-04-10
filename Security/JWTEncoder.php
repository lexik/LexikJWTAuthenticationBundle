<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security;

use Namshi\JOSE\JWS;

/**
 * JWTEncoder
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class JWTEncoder
{
    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $passPhrase;

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
     * @param array $data
     *
     * @return JWS
     */
    public function encode(array $data)
    {
        $privateKey = openssl_pkey_get_private('file://' . $this->privateKey, $this->passPhrase);

        $jws = new JWS('RS256');
        $jws->setPayload($data);
        $jws->sign($privateKey);

        return $jws;
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function decode($token)
    {
        $jws       = JWS::load($token);
        $publicKey = openssl_pkey_get_public('file://' . $this->publicKey);

        if ($jws->isValid($publicKey)) {
            return $jws->getPayload();
        }

        return false;
    }
}
