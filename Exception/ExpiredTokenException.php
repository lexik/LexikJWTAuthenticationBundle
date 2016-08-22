<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Exception;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Exception that should be thrown from a {@link JWTTokenAuthenticator} implementation during
 * an authentication process.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class ExpiredTokenException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Expired JWT Token';
    }
}
