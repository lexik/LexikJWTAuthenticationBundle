<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * LexikJWTAuthenticationBundle Configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lexik_jwt_authentication');

        $treeBuilder
            ->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('public_key')
                    ->info('The key used to sign tokens (useless for HMAC). If not set, the key will be automatically computed from the secret key.')
                    ->defaultNull()
                ->end()
                ->arrayNode('additional_public_keys')
                    ->info('Multiple public keys to try to verify token signature. If none is given, it will use the key provided in "public_key".')
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('secret_key')
                    ->info('The key used to sign tokens. It can be a raw secret (for HMAC), a raw RSA/ECDSA key or the path to a file itself being plaintext or PEM.')
                    ->defaultNull()
                ->end()
                ->scalarNode('pass_phrase')
                    ->info('The key passphrase (useless for HMAC)')
                    ->defaultValue('')
                ->end()
                ->scalarNode('token_ttl')
                    ->defaultValue(3600)
                ->end()
                ->booleanNode('allow_no_expiration')
                    ->info('Allow tokens without "exp" claim (i.e. indefinitely valid, no lifetime) to be considered valid. Caution: usage of this should be rare.')
                    ->defaultFalse()
                ->end()
                ->scalarNode('clock_skew')
                    ->defaultValue(0)
                ->end()
                ->arrayNode('encoder')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')
                            ->defaultValue('lexik_jwt_authentication.encoder.lcobucci')
                        ->end()
                        ->scalarNode('signature_algorithm')
                            ->defaultValue('RS256')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('user_id_claim')
                    ->defaultValue('username')
                    ->cannotBeEmpty()
                ->end()
                ->append($this->getTokenExtractorsNode())
                ->scalarNode('remove_token_from_body_when_cookies_used')
                    ->defaultTrue()
                ->end()
                ->arrayNode('set_cookies')
                    ->fixXmlConfig('set_cookie')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('lifetime')
                                ->defaultNull()
                                ->info('The cookie lifetime. If null, the "token_ttl" option value will be used')
                            ->end()
                            ->enumNode('samesite')
                                ->values([Cookie::SAMESITE_NONE, Cookie::SAMESITE_LAX, Cookie::SAMESITE_STRICT])
                                ->defaultValue(Cookie::SAMESITE_LAX)
                            ->end()
                            ->scalarNode('path')->defaultValue('/')->cannotBeEmpty()->end()
                            ->scalarNode('domain')->defaultNull()->end()
                            ->scalarNode('secure')->defaultTrue()->end()
                            ->scalarNode('httpOnly')->defaultTrue()->end()
                            ->arrayNode('split')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('api_platform')
                    ->canBeEnabled()
                    ->info('API Platform compatibility: add check_path in OpenAPI documentation.')
                    ->children()
                        ->scalarNode('check_path')
                            ->defaultNull()
                            ->info('The login check path to add in OpenAPI.')
                        ->end()
                        ->scalarNode('username_path')
                            ->defaultNull()
                            ->info('The path to the username in the JSON body.')
                        ->end()
                        ->scalarNode('password_path')
                            ->defaultNull()
                            ->info('The path to the password in the JSON body.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function getTokenExtractorsNode(): ArrayNodeDefinition
    {
        $builder = new TreeBuilder('token_extractors');
        $node = $builder->getRootNode();
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
                ->arrayNode('split_cookie')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('cookies')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
