<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Wires the "jwt" authenticator from user configuration.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return -10;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'jwt';
    }

    public function addConfiguration(NodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('provider')
                    ->defaultNull()
                ->end()
                ->scalarNode('authenticator')
                    ->defaultValue('lexik_jwt_authentication.security.jwt_authenticator')
                ->end()
            ->end()
        ;
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $authenticatorId = 'security.authenticator.jwt.' . $firewallName;

        $userProviderId = empty($config['provider']) ? $userProviderId : 'security.user.provider.concrete.' . $config['provider'];

        $container
            ->setDefinition($authenticatorId, new ChildDefinition($config['authenticator']))
            ->replaceArgument(3, new Reference($userProviderId))
        ;

        return $authenticatorId;
    }
}
