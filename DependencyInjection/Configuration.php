<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lexik_jwt_authentication');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('private_key_path')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('public_key_path')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('pass_phrase')
                    ->defaultValue('')
                ->end()
                ->scalarNode('token_ttl')
                    ->defaultValue(86400)
                ->end()
                ->scalarNode('encoder_service')
                    ->defaultValue('lexik_jwt_authentication.jwt_encoder')
                ->end()
                ->scalarNode('user_identity_field')
                    ->defaultValue('username')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
