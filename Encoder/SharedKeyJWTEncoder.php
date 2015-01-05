<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Namshi\JOSE\JWS;

/**
 * SharedKeyJWTEncoder
 *
 * @author Diego Blanco <diegoblaes@gmail.com>
 */
class SharedKeyJWTEncoder implements JWTEncoderInterface
{
    /**
     * @var string
     */
    protected $sharedKey;

    /**
     * @param string $sharedKey
     */
    public function __construct($algorithm, $sharedKey)
    {
        $this->algorithm = $algorithm;
        $this->sharedKey = $sharedKey;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(array $data)
    {
        $jws = new JWS($this->algorithm);
        $jws->setPayload($data);
        $jws->sign($this->getSharedKey());

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

        if (!$jws->isValid($this->getSharedKey())) {
            return false;
        }

        return $jws->getPayload();
    }

    /**
     * @return resource
     */
    protected function getSharedKey()
    {
        return $this->sharedKey;
    }
}
