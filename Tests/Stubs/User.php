<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User is the user implementation used by the in-memory user provider.
 *
 * This should not be used for anything else.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class User implements UserInterface
{
    private $userIdentifier;
    private $password;
    private $roles;
    private $email;

    public function __construct($userIdentifier, $password, $email = '', array $roles = [])
    {
        if (empty($userIdentifier)) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->userIdentifier = $userIdentifier;
        $this->password = $password;
        $this->roles = $roles;
        $this->email = $email;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function eraseCredentials(): void
    {
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
}
