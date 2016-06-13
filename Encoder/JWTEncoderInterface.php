<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Encoder;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\ExpiredJWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure\UnverifiedJWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure\UnsignedJWTEncodeFailureException;

/**
 * JWTEncoderInterface.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface JWTEncoderInterface
{
    /**
     * @param array $data
     *
     * @return string the encoded token string
     *
     * @throws UnsignedJWTEncodeFailureException
     */
    public function encode(array $data);

    /**
     * @param string $token
     *
     * @return false|array
     *
     * @throws JWTDecodeFailureException           If the signature cannot be loaded
     * @throws UnverifiedJWTDecodeFailureException If the signature cannot be verified
     * @throws ExpiredJWTDecodeFailureException    If the token is expired
     */
    public function decode($token);
}
