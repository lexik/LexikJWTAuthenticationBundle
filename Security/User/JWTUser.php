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
    /** @var non-empty-string */
    private string $userIdentifier;
    /** @var string[] */
    private array $roles;

    /**
     * @param non-empty-string $userIdentifier
     * @param string[]         $roles
     */
    public function __construct(string $userIdentifier, array $roles = [])
    {
        $this->userIdentifier = $userIdentifier;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
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

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
    }
}
