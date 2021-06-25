<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @method UserInterface loadUserByIdentifierAndPayload(string $identifier, array $payload) Loads a user from an identifier and JWT token payload.
 */
interface PayloadAwareUserProviderInterface extends UserProviderInterface
{
    /**
     * Load a user by its username, including the JWT token payload.
     *
     * @throws UsernameNotFoundException|UserNotFoundException if the user is not found
     *
     * @deprecated since 2.12, implement loadUserByIdentifierAndPayload() instead.
     */
    public function loadUserByUsernameAndPayload(string $username, array $payload)/*: UserInterface*/;
}
