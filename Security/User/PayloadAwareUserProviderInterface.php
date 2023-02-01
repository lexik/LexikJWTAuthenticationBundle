<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface PayloadAwareUserProviderInterface extends UserProviderInterface
{
    /**
     * Loads a user from an identifier and JWT token payload.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifierAndPayload(string $identifier, array $payload): UserInterface;
}
