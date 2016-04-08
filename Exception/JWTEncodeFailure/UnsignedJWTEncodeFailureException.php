<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailure;

/**
 * Exception thrown if the encoder cannot create a valid JWS.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class UnsignedJWTEncodeFailureException extends JWTEncodeFailureException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $message = 'Unable to create a signed JWT from the given configuration.',
        \Exception $previous = null
    ) {
        parent::__construct($message, $previous);
    }
}
