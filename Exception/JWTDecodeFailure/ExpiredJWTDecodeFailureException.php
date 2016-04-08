<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure;

/**
 * Exception class thrown if the given JWT is expired.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ExpiredJWTDecodeFailureException extends JWTDecodeFailureException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Expired JWT token', \Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
