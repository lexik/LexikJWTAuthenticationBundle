<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Lcobucci\JWT\Token;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Alias;
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

        if (class_exists(Application::class)) {
            $loader->load('console.xml');
        }

        $loader->load('deprecated.xml');
        $loader->load('jwt_encoder.xml');
        $loader->load('jwt_manager.xml');
        $loader->load('key_loader.xml');
        $loader->load('lcobucci.xml');
        $loader->load('namshi.xml');
        $loader->load('response_interceptor.xml');
        $loader->load('token_authenticator.xml');
        $loader->load('token_extractor.xml');

        if (!class_exists(Token::class)) {
            $container->removeDefinition('lexik_jwt_authentication.encoder.lcobucci');
            $container->removeDefinition('lexik_jwt_authentication.jws_provider.lcobucci');
        }

        $container->setParameter('lexik_jwt_authentication.private_key_path', $config['private_key_path']);
        $container->setParameter('lexik_jwt_authentication.public_key_path', $config['public_key_path']);
        $container->setParameter('lexik_jwt_authentication.pass_phrase', $config['pass_phrase']);
        $container->setParameter('lexik_jwt_authentication.token_ttl', $config['token_ttl']);
        $container->setParameter('lexik_jwt_authentication.clock_skew', $config['clock_skew']);
        $container->setParameter('lexik_jwt_authentication.user_identity_field', $config['user_identity_field']);
        $encoderConfig = $config['encoder'];
        $container->setAlias('lexik_jwt_authentication.encoder', new Alias($encoderConfig['service'], true));
        $container->setAlias(JWTEncoderInterface::class, 'lexik_jwt_authentication.encoder');
        $container->setAlias(
            'lexik_jwt_authentication.key_loader',
            new Alias('lexik_jwt_authentication.key_loader.'.('openssl' === $encoderConfig['crypto_engine'] ? $encoderConfig['crypto_engine'] : 'raw'), true)
        );

        $container->setParameter('lexik_jwt_authentication.encoder.signature_algorithm', $encoderConfig['signature_algorithm']);
        $container->setParameter('lexik_jwt_authentication.encoder.crypto_engine', $encoderConfig['crypto_engine']);

        $container
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor')
            ->replaceArgument(0, $this->createTokenExtractors($container, $config['token_extractors']));

        // Support for autowiring in symfony < 3.3
        if (!method_exists($container, 'fileExists')) {
            $this->registerAutowiringTypes($container);
        }
    }

    private static function registerAutowiringTypes(ContainerBuilder $container)
    {
        $container
            ->findDefinition('lexik_jwt_authentication.encoder.default')
            ->addAutowiringType(JWTEncoderInterface::class);

        $container
            ->getDefinition('lexik_jwt_authentication.jws_provider.default')
            ->addAutowiringType(JWSProviderInterface::class);

        $container
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor')
            ->addAutowiringType(TokenExtractorInterface::class);

        $container
            ->getDefinition('lexik_jwt_authentication.jwt_manager')
            ->addAutowiringType(JWTTokenManagerInterface::class)
            ->addAutowiringType(JWTManagerInterface::class); // To be removed in 3.0 along with the interface
    }

    private static function createTokenExtractors(ContainerBuilder $container, array $tokenExtractorsConfig)
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
