<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

interface JWTUserInterface extends UserInterface
{
    /**
     * Creates a new instance from a given JWT payload.
     *
     * @param string $username
     * @param array  $payload
     *
     * @return JWTUserInterface
     */
    public static function createFromPayload($username, array $payload);
}
