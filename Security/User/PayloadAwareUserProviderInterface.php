<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface PayloadAwareUserProviderInterface extends UserProviderInterface
{
    /**
     * Load a user by its username, including the JWT token payload.
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     * @deprecated since 2.12, implement loadByIdentifier() instead.
     */
    public function loadUserByUsernameAndPayload(string $username, array $payload)/*: UserInterface*/;

    /**
     * Load a user by its username, including the JWT token payload.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifierAndPayload(string $userIdentifier, array $payload): UserInterface;
}
