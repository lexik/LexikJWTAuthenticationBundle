<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

/**
 * User class for which to create instances from JWT tokens.
 *
 * Note: This is only useful when using the JWTUserProvider (database-less).
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTUser implements JWTUserInterface
{
    private $userIdentifier;
    private $roles;

    public function __construct(string $userIdentifier, array $roles = [])
    {
        $this->userIdentifier = $userIdentifier;
        $this->roles = $roles;
    }

    public static function createFromPayload($username, array $payload)
    {
        if (isset($payload['roles'])) {
            return new static($username, (array) $payload['roles']);
        }

        return new static($username);
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
