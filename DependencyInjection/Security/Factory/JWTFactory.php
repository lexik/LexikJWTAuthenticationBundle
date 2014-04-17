<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * JWTFactory
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.jwt.' . $id;
        $container
            ->setDefinition($providerId, new DefinitionDecorator('lexik_jwt_authentication.security.authentication.provider'))
            ->replaceArgument(0, new Reference($userProvider));

        $listenerId = 'security.authentication.listener.jwt.' . $id;
        $container
            ->setDefinition($listenerId, new DefinitionDecorator('lexik_jwt_authentication.security.authentication.listener'));

        if ($config['authorization_header']['enabled']) {

            $authorizationHeaderExtractorId = 'lexik_jwt_authentication.extractor.authorization_header_extractor.' . $id;
            $container
                ->setDefinition($authorizationHeaderExtractorId, new DefinitionDecorator('lexik_jwt_authentication.extractor.authorization_header_extractor'))
                ->replaceArgument(0, $config['authorization_header']['prefix']);

            $container
                ->getDefinition($listenerId)
                ->addMethodCall('addTokenExtractor', array(new Reference($authorizationHeaderExtractorId)));

        }

        if ($config['query_parameter']['enabled']) {

            $queryParameterExtractorId = 'lexik_jwt_authentication.extractor.query_parameter_extractor.' . $id;
            $container
                ->setDefinition($queryParameterExtractorId, new DefinitionDecorator('lexik_jwt_authentication.extractor.query_parameter_extractor'))
                ->replaceArgument(0, $config['query_parameter']['name']);

            $container
                ->getDefinition($listenerId)
                ->addMethodCall('addTokenExtractor', array(new Reference($queryParameterExtractorId)));

        }

        return array($providerId, $listenerId, $defaultEntryPoint);
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
        $node
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
            ->end();
    }
}
