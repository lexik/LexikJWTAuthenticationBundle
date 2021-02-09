<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;

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
        $treeBuilder = new TreeBuilder('lexik_jwt_authentication');
        $rootNode    = \method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('lexik_jwt_authentication');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('private_key_path')
                    ->setDeprecated(...$this->getDeprecationParameters('The "%path%.%node%" configuration key is deprecated since version 2.5. Use "%path%.secret_key" instead.', '2.5'))
                    ->defaultNull()
                ->end()
                ->scalarNode('public_key_path')
                    ->setDeprecated(...$this->getDeprecationParameters('The "%path%.%node%" configuration key is deprecated since version 2.5. Use "%path%.public_key" instead.', '2.5'))
                    ->defaultNull()
                ->end()
                ->scalarNode('public_key')
                    ->info('The key used to sign tokens (useless for HMAC). If not set, the key will be automatically computed from the secret key.')
                    ->defaultNull()
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
                        ->enumNode('crypto_engine')
                            ->values(['openssl', 'phpseclib'])
                            ->defaultValue('openssl')
                            ->setDeprecated(...$this->getDeprecationParameters('The "%path%.%node%" configuration key is deprecated since version 2.5, built-in encoders support OpenSSL only', '2.5'))
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('user_identity_field')
                    ->defaultValue('username')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('user_id_claim')
                    ->defaultNull()
                    ->info('If null, the user ID claim will have the same name as the one defined by the option "user_identity_field"')
                ->end()
                ->append($this->getTokenExtractorsNode())
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
            ->end();

        return $treeBuilder;
    }

    private function getTokenExtractorsNode()
    {
        $builder = new TreeBuilder('token_extractors');
        $node = \method_exists(TreeBuilder::class, 'getRootNode') ? $builder->getRootNode() : $builder->root('token_extractors');
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

    /**
     * Returns the correct deprecation parameters for setDeprecated.
     *
     * @param string $message
     * @param string $version
     *
     * @return string[]
     */
    private function getDeprecationParameters($message, $version)
    {
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return ['lexik/jwt-authentication-bundle', $version, $message];
        }

        return [$message];
    }
}
