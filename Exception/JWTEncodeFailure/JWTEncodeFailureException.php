<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure;

/**
 * Base class for exceptions thrown during JWTEncoderInterface::encode().
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTEncodeFailureException extends \Exception
{
    /**
     * @param string     $message
     * @param \Exception $previous
     */
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
