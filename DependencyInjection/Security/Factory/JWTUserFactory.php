<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * Creates the `lexik_jwt` user provider.
 *
 * @internal
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class JWTUserFactory implements UserProviderFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config)
    {
        $definition = $container->setDefinition($id, new DefinitionDecorator('lexik_jwt_authentication.security.jwt_user_provider'));
        $definition->replaceArgument(0, $config['class']);
    }

    public function getKey()
    {
        return 'lexik_jwt';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('class')
                    ->cannotBeEmpty()
                    ->defaultValue(JWTUser::class)
                    ->validate()
                        ->ifTrue(function ($class) {
                            return !(new \ReflectionClass($class))->implementsInterface(JWTUserInterface::class);
                        })
                        ->thenInvalid('The %s class must implement '.JWTUserInterface::class.' for using the "lexik_jwt" user provider.')
                    ->end()
                ->end()
            ->end()
        ;
    }
}
