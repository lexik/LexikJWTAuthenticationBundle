<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailure;

/**
 * Exception class thrown if the JWS cannot be verified.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class UnverifiedJWTDecodeFailureException extends JWTDecodeFailureException
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        $message = 'Unable to verify the given JWT through the given configuration. If the "lexik_jwt_authentication.encoder" encrytion options have been changed since your last authentication, please renew the token. If the problem persists, verify that the configured keys/passphrase are valid.',
        \Exception $previous = null
    ) {
        parent::__construct($message, $previous);
    }
}
