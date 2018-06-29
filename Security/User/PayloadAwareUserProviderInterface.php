<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface PayloadAwareUserProviderInterface extends UserProviderInterface
{
    /**
     * Load a user by its username, including the JWT token payload.
     *
     * @param string $username
     * @param array  $payload
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     * @return UserInterface
     */
    public function loadUserByUsernameAndPayload($username, array $payload);
}
