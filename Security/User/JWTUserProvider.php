<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWT User provider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTUserProvider implements PayloadAwareUserProviderInterface
{
    private $class;

    private $cache = [];

    /**
     * @param string $class The {@link JWTUserInterface} implementation FQCN for which to provide instances
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $payload The JWT payload from which to create an instance
     *
     * @return UserInterface
     */
    public function loadUserByUsername($username, array $payload = [])
    {
        return $this->loadUserByUsernameAndPayload($username, $payload);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $payload The JWT payload from which to create an instance
     */
    public function loadUserByIdentifier(string $identifier, array $payload = []): UserInterface
    {
        return $this->loadUserByIdentifierAndPayload($identifier, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsernameAndPayload(string $username, array $payload): UserInterface
    {
        if (isset($this->cache[$username])) {
            return $this->cache[$username];
        }

        $class = $this->class;

        return $this->cache[$username] = $class::createFromPayload($username, $payload);
    }

    public function loadUserByIdentifierAndPayload(string $userIdentifier, array $payload): UserInterface
    {
        if (isset($this->cache[$userIdentifier])) {
            return $this->cache[$userIdentifier];
        }

        $class = $this->class;

        return $this->cache[$userIdentifier] = $class::createFromPayload($userIdentifier, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class): bool
    {
        return $class === $this->class || (new \ReflectionClass($class))->implementsInterface(JWTUserInterface::class);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user; // noop
    }
}
