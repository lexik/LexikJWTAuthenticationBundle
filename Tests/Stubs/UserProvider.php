<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides a disabled user that is not possible to create via yaml configuration
 */
final class UserProvider implements UserProviderInterface, ResetInterface
{
    private const DEFAULT_USERS = [
        'lexik_disabled' => [
            'username' => 'lexik_disabled',
            'password' => 'dummy',
            'roles' => ['ROLE_USER'],
            'enabled' => true,
        ]
    ];

    /**
     * Are users enbled ?
     */
    public static $enabled = false;
    public static $users = self::DEFAULT_USERS;

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->getUser($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return InMemoryUser::class === $class;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->getUser($username);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->getUser($identifier);
    }

    private function getUser(string $username): InMemoryUser
    {
        $user = self::$users[strtolower($username)] ?? null;
        if (null === $user) {
            $ex = new UserNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUserIdentifier($username);

            throw $ex;
        }

        return new InMemoryUser($user['username'], $user['password'], $user['roles'], $user['enabled']);
    }

    public function reset(): void
    {
        self::$users = self::DEFAULT_USERS;
    }
}
