<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTManagerInterface
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
interface JWTManagerInterface
{
    /**
     * @param UserInterface $user
     *
     * @return string
     */
    public function create(UserInterface $user);

    /**
     * @param TokenInterface $token
     *
     * @return bool|array
     */
    public function decode(TokenInterface $token);
}
