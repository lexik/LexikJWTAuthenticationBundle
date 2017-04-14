<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * LexikJWTAuthenticationBundle Configuration.
 */
class Configuration implements ConfigurationInterface
{
    const INVALID_KEY_PATH = "The file %s doesn't exist or is not readable.\nIf the configured encoder doesn't need this to be configured, please don't set this option or leave it null.";

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
                ->end()
                ->scalarNode('public_key_path')
                    ->defaultNull()
                ->end()
                ->scalarNode('pass_phrase')
                    ->defaultValue('')
                ->end()
                ->scalarNode('token_ttl')
                    ->defaultValue(3600)
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
                        ->enumNode('crypto_engine')
                            ->values(['openssl', 'phpseclib'])
                            ->defaultValue('openssl')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('user_identity_field')
                    ->defaultValue('username')
                    ->cannotBeEmpty()
                ->end()
                ->append($this->getTokenExtractorsNode())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return TreeBuilder
     */
    private function getTokenExtractorsNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('token_extractors');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('authorization_header')
                ->addDefaultsIfNotSet()
                ->canBeDisabled()
                    ->children()
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
                ->canBeEnabled()
                    ->children()
                        ->scalarNode('name')
                            ->defaultValue('BEARER')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query_parameter')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('name')
                            ->defaultValue('bearer')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
