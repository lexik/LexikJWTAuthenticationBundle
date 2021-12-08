<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
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
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (method_exists(Alias::class, 'getDeprecation')) {
            $loader->load('deprecated_51.xml');
        } else {
            $loader->load('deprecated.xml');
        }
        $loader->load('jwt_manager.xml');
        $loader->load('key_loader.xml');
        $loader->load('namshi.xml');
        $loader->load('lcobucci.xml');
        $loader->load('response_interceptor.xml');
        $loader->load('token_authenticator.xml');
        $loader->load('token_extractor.xml');
        $loader->load('guard_authenticator.xml');

        if (isset($config['private_key_path'])) {
            $config['secret_key'] = $config['private_key_path'];
            $container->setParameter('lexik_jwt_authentication.private_key_path', $config['secret_key']);
        }

        if (isset($config['public_key_path'])) {
            $config['public_key'] = $config['public_key_path'];
            $container->setParameter('lexik_jwt_authentication.public_key_path', $config['public_key']);
        }

        if (empty($config['public_key']) && empty($config['secret_key'])) {
            $e = new InvalidConfigurationException('You must either configure a "public_key" or a "secret_key".');
            $e->setPath('lexik_jwt_authentication');

            throw $e;
        }

        $container->setParameter('lexik_jwt_authentication.pass_phrase', $config['pass_phrase']);
        $container->setParameter('lexik_jwt_authentication.token_ttl', $config['token_ttl']);
        $container->setParameter('lexik_jwt_authentication.clock_skew', $config['clock_skew']);
        $container->setParameter('lexik_jwt_authentication.user_identity_field', $config['user_identity_field']);

        $user_id_claim = $config['user_id_claim'] ?: $config['user_identity_field'];
        $container->setParameter('lexik_jwt_authentication.user_id_claim', $user_id_claim);
        $encoderConfig = $config['encoder'];

        if ('lexik_jwt_authentication.encoder.default' === $encoderConfig['service']) {
            @trigger_error('Using "lexik_jwt_authentication.encoder.default" as encoder service is deprecated since LexikJWTAuthenticationBundle 2.5, use "lexik_jwt_authentication.encoder.lcobucci" (default) or your own encoder service instead.', E_USER_DEPRECATED);
        }

        $container->setAlias('lexik_jwt_authentication.encoder', new Alias($encoderConfig['service'], true));
        $container->setAlias(JWTEncoderInterface::class, 'lexik_jwt_authentication.encoder');
        $container->setAlias(
            'lexik_jwt_authentication.key_loader',
            new Alias('lexik_jwt_authentication.key_loader.'.('openssl' === $encoderConfig['crypto_engine'] && 'lexik_jwt_authentication.encoder.default' === $encoderConfig['service'] ? $encoderConfig['crypto_engine'] : 'raw'), true)
        );

        $container
            ->findDefinition('lexik_jwt_authentication.key_loader')
            ->replaceArgument(0, $config['secret_key'])
            ->replaceArgument(1, $config['public_key']);

        if (isset($config['additional_public_keys'])) {
            $container
                ->findDefinition('lexik_jwt_authentication.key_loader')
                ->replaceArgument(3, $config['additional_public_keys']);
        }

        $container->setParameter('lexik_jwt_authentication.encoder.signature_algorithm', $encoderConfig['signature_algorithm']);
        $container->setParameter('lexik_jwt_authentication.encoder.crypto_engine', $encoderConfig['crypto_engine']);

        $tokenExtractors = $this->createTokenExtractors($container, $config['token_extractors']);
        $container
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor')
            ->replaceArgument(0, $tokenExtractors);

        if (false === $config['remove_token_from_body_when_cookies_used']) {
            $container
                ->getDefinition('lexik_jwt_authentication.handler.authentication_success')
                ->replaceArgument(3, false);
        }

        if ($config['set_cookies']) {
            $loader->load('cookie.xml');

            $cookieProviders = [];
            foreach ($config['set_cookies'] as $name => $attributes) {
                $container
                    ->setDefinition($id = "lexik_jwt_authentication.cookie_provider.$name", new ChildDefinition('lexik_jwt_authentication.cookie_provider'))
                    ->replaceArgument(0, $name)
                    ->replaceArgument(1, $attributes['lifetime'] ?? ($config['token_ttl'] ?: 0))
                    ->replaceArgument(2, $attributes['samesite'])
                    ->replaceArgument(3, $attributes['path'])
                    ->replaceArgument(4, $attributes['domain'])
                    ->replaceArgument(5, $attributes['secure'])
                    ->replaceArgument(6, $attributes['httpOnly'])
                    ->replaceArgument(7, $attributes['split']);
                $cookieProviders[] = new Reference($id);
            }

            $container
                ->getDefinition('lexik_jwt_authentication.handler.authentication_success')
                ->replaceArgument(2, new IteratorArgument($cookieProviders));
        }

        if (class_exists(Application::class)) {
            $loader->load('console.xml');

            $container
                ->getDefinition('lexik_jwt_authentication.generate_keypair_command')
                ->replaceArgument(1, $config['secret_key'])
                ->replaceArgument(2, $config['public_key'])
                ->replaceArgument(3, $config['pass_phrase'])
                ->replaceArgument(4, $encoderConfig['signature_algorithm']);
        }

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

        if ($tokenExtractorsConfig['split_cookie']['enabled']) {
            $cookieExtractorId = 'lexik_jwt_authentication.extractor.split_cookie_extractor';
            $container
                ->getDefinition($cookieExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['split_cookie']['cookies']);

            $map[] = new Reference($cookieExtractorId);
        }

        return $map;
    }
}
