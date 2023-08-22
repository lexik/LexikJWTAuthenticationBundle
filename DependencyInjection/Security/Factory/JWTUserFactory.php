<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use ReflectionClass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Creates the `lexik_jwt` user provider.
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTUserFactory implements UserProviderFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config): void
    {
        $container->setDefinition($id, new ChildDefinition('lexik_jwt_authentication.security.jwt_user_provider'))
            ->replaceArgument(0, $config['class']);
    }

    public function getKey(): string
    {
        return 'lexik_jwt';
    }

    public function addConfiguration(NodeDefinition $node): void
    {
        $node
            ->children()
                ->scalarNode('class')
                    ->cannotBeEmpty()
                    ->defaultValue(JWTUser::class)
                    ->validate()
                        ->ifTrue(fn ($class) => !(new ReflectionClass($class))->implementsInterface(JWTUserInterface::class))
                        ->thenInvalid('The %s class must implement ' . JWTUserInterface::class . ' for using the "lexik_jwt" user provider.')
                    ->end()
                ->end()
            ->end()
        ;
    }
}
