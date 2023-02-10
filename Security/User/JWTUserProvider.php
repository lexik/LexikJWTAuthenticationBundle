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
    private string $class;

    private array $cache = [];

    /**
     * @param string $class The {@link JWTUserInterface} implementation FQCN for which to provide instances
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * To be removed at the same time as symfony 5.4 support.
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        // to be removed at the same time as symfony 5.4 support
        throw new \LogicException('This method is implemented for BC purpose and should never be called.');
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

    public function loadUserByIdentifierAndPayload(string $identifier, array $payload): UserInterface
    {
        if (isset($this->cache[$identifier])) {
            return $this->cache[$identifier];
        }

        $class = $this->class;

        return $this->cache[$identifier] = $class::createFromPayload($identifier, $payload);
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
