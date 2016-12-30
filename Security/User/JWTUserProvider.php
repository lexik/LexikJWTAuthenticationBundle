<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * JWT User provider.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTUserProvider implements UserProviderInterface
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
     * @return JWTUserInterface
     */
    public function loadUserByUsername($username, array $payload = [])
    {
        $class = $this->class;

        if (isset($this->cache[$username])) {
            return $this->cache[$username];
        }

        return $this->cache[$username] = $class::createFromPayload($username, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->class || (new \ReflectionClass($class))->implementsInterface(JWTUserInterface::class);
    }

    public function refreshUser(UserInterface $user)
    {
        // noop
    }
}
