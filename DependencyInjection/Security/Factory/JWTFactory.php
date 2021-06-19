<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * JWTFactory.
 *
 * @deprecated since 2.0, use the "lexik_jwt_authentication.jwt_token_authenticator" Guard
 * authenticator instead
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTFactory implements SecurityFactoryInterface
{
    public function __construct($triggerDeprecation = true)
    {
        if ($triggerDeprecation) {
            trigger_deprecation('lexik/jwt-authentication-bundle', '2.0', 'Class "%s" is deprecated, use "%s" instead.', self::class, JWTAuthenticatorFactory::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.jwt.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition($config['authentication_provider']))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.jwt.'.$id;
        $container
            ->setDefinition($listenerId, new ChildDefinition($config['authentication_listener']))
            ->replaceArgument(2, $config);

        $entryPointId = $defaultEntryPoint;

        if ($config['create_entry_point']) {
            $entryPointId = $this->createEntryPoint($container, $id, $defaultEntryPoint);
        }

        if ($config['authorization_header']['enabled']) {
            $authorizationHeaderExtractorId = 'lexik_jwt_authentication.extractor.authorization_header_extractor.'.$id;
            $container
                ->setDefinition($authorizationHeaderExtractorId, new ChildDefinition('lexik_jwt_authentication.extractor.authorization_header_extractor'))
                ->replaceArgument(0, $config['authorization_header']['prefix'])
                ->replaceArgument(1, $config['authorization_header']['name']);

            $container
                ->getDefinition($listenerId)
                ->addMethodCall('addTokenExtractor', [new Reference($authorizationHeaderExtractorId)]);
        }

        if ($config['query_parameter']['enabled']) {
            $queryParameterExtractorId = 'lexik_jwt_authentication.extractor.query_parameter_extractor.'.$id;
            $container
                ->setDefinition($queryParameterExtractorId, new ChildDefinition('lexik_jwt_authentication.extractor.query_parameter_extractor'))
                ->replaceArgument(0, $config['query_parameter']['name']);

            $container
                ->getDefinition($listenerId)
                ->addMethodCall('addTokenExtractor', [new Reference($queryParameterExtractorId)]);
        }

        if ($config['cookie']['enabled']) {
            $cookieExtractorId = 'lexik_jwt_authentication.extractor.cookie_extractor.'.$id;
            $container
                ->setDefinition($cookieExtractorId, new ChildDefinition('lexik_jwt_authentication.extractor.cookie_extractor'))
                ->replaceArgument(0, $config['cookie']['name']);

            $container
                ->getDefinition($listenerId)
                ->addMethodCall('addTokenExtractor', [new Reference($cookieExtractorId)]);
        }

        return [$providerId, $listenerId, $entryPointId];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'lexik_jwt';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $deprecationArgs = ['The "%path%.%node%" configuration key is deprecated. Use the "lexik_jwt_authentication.jwt_token_authenticator" Guard authenticator instead.'];
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            $deprecationArgs = ['lexik/jwt-authentication-bundle', '2.7', 'The "%path%.%node%" configuration key is deprecated. Use the "lexik_jwt_authentication.jwt_token_authenticator" Guard authenticator instead.'];
        }

        $node
            ->setDeprecated(...$deprecationArgs)
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
                ->canBeEnabled()
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')
                            ->defaultValue('bearer')
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('throw_exceptions')
                    ->defaultFalse()
                ->end()
                ->booleanNode('create_entry_point')
                    ->defaultTrue()
                ->end()
                ->scalarNode('authentication_provider')
                    ->defaultValue('lexik_jwt_authentication.security.authentication.provider')
                ->end()
                ->scalarNode('authentication_listener')
                    ->defaultValue('lexik_jwt_authentication.security.authentication.listener')
                ->end()
            ->end();
    }

    /**
     * Create an entry point, by default it sends a 401 header and ends the request.
     *
     * @param string $id
     * @param mixed  $defaultEntryPoint
     *
     * @return string
     */
    protected function createEntryPoint(ContainerBuilder $container, $id, $defaultEntryPoint)
    {
        $entryPointId = 'lexik_jwt_authentication.security.authentication.entry_point.'.$id;
        $container->setDefinition($entryPointId, new ChildDefinition('lexik_jwt_authentication.security.authentication.entry_point'));

        return $entryPointId;
    }
}
