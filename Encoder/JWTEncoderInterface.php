<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

/**
 * JWTEncoderInterface
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface JWTEncoderInterface
{
    /**
     * @param array $data
     *
     * @return string the encoded token string
     */
    public function encode(array $data);

    /**
     * @param string $token
     *
     * @return bool|array
     */
    public function decode($token);
}
