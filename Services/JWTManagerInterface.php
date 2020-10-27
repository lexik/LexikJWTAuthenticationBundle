<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTManagerInterface.
 *
 * @deprecated since 2.0, removed in 3.0. Use {@link JWTTokenManagerInterface} instead
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface JWTManagerInterface
{
    /**
     * @param UserInterface $user
     *
     * @return string The JWT token
     */
    public function create(UserInterface $user);

    /**
     * @param TokenInterface $token
     *
     * @return array|false The JWT token payload or false if an error occurs
     */
    public function decode(TokenInterface $token);
}
