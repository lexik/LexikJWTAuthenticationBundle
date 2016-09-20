<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Exception to be thrown in case of invalid token during an authentication process.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class InvalidTokenException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid JWT Token';
    }
}
