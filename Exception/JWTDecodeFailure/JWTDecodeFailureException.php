<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure;

/**
 * Base class for exceptions thrown during JWTEncoderInterface::decode().
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTDecodeFailureException extends \Exception
{
    /**
     * @param string     $message
     * @param \Exception $previous
     */
    public function __construct($message = null, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
