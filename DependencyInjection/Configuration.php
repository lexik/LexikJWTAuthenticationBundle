<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('lexik_jwt_authentication');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('private_key_path')
                    ->defaultNull()
                    ->validate()
                    ->ifString()
                        ->then(self::validateKeyPath())
                    ->end()
                ->end()
                ->scalarNode('public_key_path')
                    ->defaultNull()
                    ->validate()
                    ->ifString()
                        ->then(self::validateKeyPath())
                    ->end()
                ->end()
                ->scalarNode('pass_phrase')
                    ->defaultValue('')
                ->end()
                ->scalarNode('token_ttl')
                    ->defaultValue(86400)
                ->end()
                ->arrayNode('encoder')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')
                            ->defaultValue('lexik_jwt_authentication.encoder.default')
                        ->end()
                        ->scalarNode('signature_algorithm')
                            ->defaultValue('RS256')
                            ->cannotBeEmpty()
                        ->end()
                        ->enumNode('encryption_engine')
                            ->values(['openssl', 'phpseclib'])
                            ->defaultValue('openssl')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('user_identity_field')
                    ->defaultValue('username')
                    ->cannotBeEmpty()
                ->end()
                ->append(self::getTokenExtractorsNode())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return TreeBuilder
     */
    private static function getTokenExtractorsNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('token_extractors');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('authorization_header')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('prefix')
                            ->defaultValue('Bearer')
                        ->end()
                        ->scalarNode('name')
                            ->defaultValue('Authorization')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cookie')
                ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('name')
                            ->defaultValue('BEARER')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query_parameter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('name')
                            ->defaultValue('bearer')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return \Closure
     */
    private static function validateKeyPath()
    {
        return function ($path) {
            if (!is_file($path) || !is_readable($path)) {
                throw new \InvalidArgumentException(sprintf('The file "%s" doesn\'t exist or is not readable.%sIf the configured encoder doesn\'t need this to be configured, please don\'t set this option or leave it null.', $path, PHP_EOL));
            }

            return $path;
        };
    }
}
