<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LexikJWTAuthenticationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('lexik_jwt_authentication.private_key_path', $config['private_key_path']);
        $container->setParameter('lexik_jwt_authentication.public_key_path', $config['public_key_path']);
        $container->setParameter('lexik_jwt_authentication.pass_phrase', $config['pass_phrase']);
        $container->setParameter('lexik_jwt_authentication.token_ttl', $config['token_ttl']);
        $container->setParameter('lexik_jwt_authentication.user_identity_field', $config['user_identity_field']);

        $encoderConfig = $config['encoder'];
        $container->setAlias('lexik_jwt_authentication.encoder', $encoderConfig['service']);
        $container->setAlias(
            'lexik_jwt_authentication.key_loader',
            'lexik_jwt_authentication.key_loader.'.('openssl' === $encoderConfig['crypto_engine'] ? $encoderConfig['crypto_engine'] : 'raw')
        );
        $container->setParameter('lexik_jwt_authentication.encoder.signature_algorithm', $encoderConfig['signature_algorithm']);
        $container->setParameter('lexik_jwt_authentication.encoder.crypto_engine', $encoderConfig['crypto_engine']);

        $container
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor')
            ->replaceArgument(0, $this->createTokenExtractors($container, $config['token_extractors']));
    }

    private function createTokenExtractors(ContainerBuilder $container, array $tokenExtractorsConfig)
    {
        $map = [];

        if ($tokenExtractorsConfig['authorization_header']['enabled']) {
            $authorizationHeaderExtractorId = 'lexik_jwt_authentication.extractor.authorization_header_extractor';
            $container
                ->getDefinition($authorizationHeaderExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['authorization_header']['prefix'])
                ->replaceArgument(1, $tokenExtractorsConfig['authorization_header']['name']);

            $map[] = new Reference($authorizationHeaderExtractorId);
        }

        if ($tokenExtractorsConfig['query_parameter']['enabled']) {
            $queryParameterExtractorId = 'lexik_jwt_authentication.extractor.query_parameter_extractor';
            $container
                ->getDefinition($queryParameterExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['query_parameter']['name']);

            $map[] = new Reference($queryParameterExtractorId);
        }

        if ($tokenExtractorsConfig['cookie']['enabled']) {
            $cookieExtractorId = 'lexik_jwt_authentication.extractor.cookie_extractor';
            $container
                ->getDefinition($cookieExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['cookie']['name']);

            $map[] = new Reference($cookieExtractorId);
        }

        return $map;
    }
}
