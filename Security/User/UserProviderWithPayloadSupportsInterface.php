<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface UserProviderWithPayloadSupportsInterface extends UserProviderInterface
{
    /**
     * Load a user by its username, including the JWT token payload.
     *
     * @param string $username
     * @param array $payload
     *
     * @return UserInterface
     */
    public function loadUserByUsernameAndPayload(string $username, array $payload) : UserInterface;
}
