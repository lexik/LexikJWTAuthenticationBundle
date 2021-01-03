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
    private $username;
    private $roles;

    /**
     * @final
     */
    public function __construct(string $username, array $roles = [])
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromPayload($username, array $payload)
    {
        if (isset($payload['roles'])) {
            return new static($username, (array) $payload['roles']);
        }

        return new static($username);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
