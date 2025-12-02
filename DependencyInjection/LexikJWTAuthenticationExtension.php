<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;

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
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('jwt_manager.php');
        $loader->load('key_loader.php');
        $loader->load('lcobucci.php');
        $loader->load('response_interceptor.php');
        $loader->load('token_authenticator.php');
        $loader->load('token_extractor.php');

        if (empty($config['public_key']) && empty($config['secret_key'])) {
            $e = new InvalidConfigurationException('You must either configure a "public_key" or a "secret_key".');
            $e->setPath('lexik_jwt_authentication');

            throw $e;
        }

        $container->setParameter('lexik_jwt_authentication.pass_phrase', $config['pass_phrase']);
        $container->setParameter('lexik_jwt_authentication.token_ttl', $config['token_ttl']);
        $container->setParameter('lexik_jwt_authentication.clock_skew', $config['clock_skew']);
        $container->setParameter('lexik_jwt_authentication.allow_no_expiration', $config['allow_no_expiration']);
        $container->setParameter('lexik_jwt_authentication.user_id_claim', $config['user_id_claim']);

        $encoderConfig = $config['encoder'];

        $container->setAlias('lexik_jwt_authentication.encoder', new Alias($encoderConfig['service'], true));
        $container->setAlias(JWTEncoderInterface::class, 'lexik_jwt_authentication.encoder');
        $container->setAlias(
            'lexik_jwt_authentication.key_loader',
            new Alias('lexik_jwt_authentication.key_loader.raw', true)
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

        $tokenExtractors = $this->createTokenExtractors($container, $config['token_extractors']);
        $container
            ->getDefinition('lexik_jwt_authentication.extractor.chain_extractor')
            ->replaceArgument(0, $tokenExtractors);

        if (isset($config['remove_token_from_body_when_cookies_used'])) {
            $container
                ->getDefinition('lexik_jwt_authentication.handler.authentication_success')
                ->replaceArgument(3, $config['remove_token_from_body_when_cookies_used']);
        }

        if ($config['set_cookies']) {
            $loader->load('cookie.php');

            $cookieProviders = [];
            foreach ($config['set_cookies'] as $name => $attributes) {
                if ($attributes['partitioned'] && Kernel::VERSION < '6.4') {
                    throw new \LogicException(sprintf('The `partitioned` option for cookies is only available for Symfony 6.4 and above. You are currently on version %s', Kernel::VERSION));
                }
                
                $container
                    ->setDefinition($id = "lexik_jwt_authentication.cookie_provider.$name", new ChildDefinition('lexik_jwt_authentication.cookie_provider'))
                    ->replaceArgument(0, $name)
                    ->replaceArgument(1, $attributes['lifetime'] ?? ($config['token_ttl'] ?: 0))
                    ->replaceArgument(2, $attributes['samesite'])
                    ->replaceArgument(3, $attributes['path'])
                    ->replaceArgument(4, $attributes['domain'])
                    ->replaceArgument(5, $attributes['secure'])
                    ->replaceArgument(6, $attributes['httpOnly'])
                    ->replaceArgument(7, $attributes['split'])
                    ->replaceArgument(8, $attributes['partitioned']);
                $cookieProviders[] = new Reference($id);
            }

            $container
                ->getDefinition('lexik_jwt_authentication.handler.authentication_success')
                ->replaceArgument(2, new IteratorArgument($cookieProviders));
        }

        if (class_exists(Application::class)) {
            $loader->load('console.php');

            $container
                ->getDefinition('lexik_jwt_authentication.generate_keypair_command')
                ->replaceArgument(1, $config['secret_key'])
                ->replaceArgument(2, $config['public_key'])
                ->replaceArgument(3, $config['pass_phrase'])
                ->replaceArgument(4, $encoderConfig['signature_algorithm']);
            if (!$container->hasParameter('kernel.debug') || !$container->getParameter('kernel.debug')) {
                $container->removeDefinition('lexik_jwt_authentication.migrate_config_command');
            }
        }

        if ($this->isConfigEnabled($container, $config['api_platform'])) {
            if (!class_exists(ApiPlatformBundle::class)) {
                throw new LogicException('API Platform cannot be detected. Try running "composer require api-platform/core".');
            }

            $loader->load('api_platform.php');

            $container
                ->getDefinition('lexik_jwt_authentication.api_platform.openapi.factory')
                ->replaceArgument(1, $config['api_platform']['check_path'] ?? null)
                ->replaceArgument(2, $config['api_platform']['username_path'] ?? null)
                ->replaceArgument(3, $config['api_platform']['password_path'] ?? null);
        }

        $this->processWithWebTokenConfig($config, $container, $loader);

        if ($this->isConfigEnabled($container, $config['blocklist_token'])) {
            $loader->load('blocklist_token.php');
            $blockListTokenConfig = $config['blocklist_token'];
            $container->setAlias('lexik_jwt_authentication.blocklist_token.cache', $blockListTokenConfig['cache']);
        } else {
            $container->getDefinition('lexik_jwt_authentication.payload_enrichment.random_jti_enrichment')
                ->clearTag('lexik_jwt_authentication.payload_enrichment');
        }
    }

    private function createTokenExtractors(ContainerBuilder $container, array $tokenExtractorsConfig): array
    {
        $map = [];

        if ($this->isConfigEnabled($container, $tokenExtractorsConfig['authorization_header'])) {
            $authorizationHeaderExtractorId = 'lexik_jwt_authentication.extractor.authorization_header_extractor';
            $container
                ->getDefinition($authorizationHeaderExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['authorization_header']['prefix'])
                ->replaceArgument(1, $tokenExtractorsConfig['authorization_header']['name']);

            $map[] = new Reference($authorizationHeaderExtractorId);
        }

        if ($this->isConfigEnabled($container, $tokenExtractorsConfig['query_parameter'])) {
            $queryParameterExtractorId = 'lexik_jwt_authentication.extractor.query_parameter_extractor';
            $container
                ->getDefinition($queryParameterExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['query_parameter']['name']);

            $map[] = new Reference($queryParameterExtractorId);
        }

        if ($this->isConfigEnabled($container, $tokenExtractorsConfig['cookie'])) {
            $cookieExtractorId = 'lexik_jwt_authentication.extractor.cookie_extractor';
            $container
                ->getDefinition($cookieExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['cookie']['name']);

            $map[] = new Reference($cookieExtractorId);
        }

        if ($this->isConfigEnabled($container, $tokenExtractorsConfig['split_cookie'])) {
            $cookieExtractorId = 'lexik_jwt_authentication.extractor.split_cookie_extractor';
            $container
                ->getDefinition($cookieExtractorId)
                ->replaceArgument(0, $tokenExtractorsConfig['split_cookie']['cookies']);

            $map[] = new Reference($cookieExtractorId);
        }

        return $map;
    }

    private function processWithWebTokenConfig(array $config, ContainerBuilder $container, LoaderInterface $loader): void
    {
        if ($config['access_token_issuance']['enabled'] === false && $config['access_token_verification']['enabled'] === false) {
            return;
        }
        $loader->load('web_token.php');
        if ($config['access_token_issuance']['enabled'] === true) {
            $loader->load('web_token_issuance.php');
            $accessTokenBuilder = 'lexik_jwt_authentication.access_token_builder';
            $accessTokenBuilderDefinition = $container->getDefinition($accessTokenBuilder);
            $accessTokenBuilderDefinition
                ->replaceArgument(3, $config['access_token_issuance']['signature']['algorithm'])
                ->replaceArgument(4, $config['access_token_issuance']['signature']['key'])
            ;
            if ($config['access_token_issuance']['encryption']['enabled'] === true) {
                $accessTokenBuilderDefinition
                    ->replaceArgument(5, $config['access_token_issuance']['encryption']['key_encryption_algorithm'])
                    ->replaceArgument(6, $config['access_token_issuance']['encryption']['content_encryption_algorithm'])
                    ->replaceArgument(7, $config['access_token_issuance']['encryption']['key'])
                ;
            }
        }
        if ($config['access_token_verification']['enabled'] === true) {
            $loader->load('web_token_verification.php');
            $accessTokenLoader = 'lexik_jwt_authentication.access_token_loader';
            $accessTokenLoaderDefinition = $container->getDefinition($accessTokenLoader);
            $accessTokenLoaderDefinition
                ->replaceArgument(3, $config['access_token_verification']['signature']['claim_checkers'])
                ->replaceArgument(4, $config['access_token_verification']['signature']['header_checkers'])
                ->replaceArgument(5, $config['access_token_verification']['signature']['mandatory_claims'])
                ->replaceArgument(6, $config['access_token_verification']['signature']['allowed_algorithms'])
                ->replaceArgument(7, $config['access_token_verification']['signature']['keyset'])
            ;
            if ($config['access_token_verification']['encryption']['enabled'] === true) {
                $accessTokenLoaderDefinition
                    ->replaceArgument(8, $config['access_token_verification']['encryption']['continue_on_decryption_failure'])
                    ->replaceArgument(9, $config['access_token_verification']['encryption']['header_checkers'])
                    ->replaceArgument(10, $config['access_token_verification']['encryption']['allowed_key_encryption_algorithms'])
                    ->replaceArgument(11, $config['access_token_verification']['encryption']['allowed_content_encryption_algorithms'])
                    ->replaceArgument(12, $config['access_token_verification']['encryption']['keyset'])
                ;
            } else {
                $accessTokenLoaderDefinition
                    ->replaceArgument(8, null)
                    ->replaceArgument(9, null)
                    ->replaceArgument(10, null)
                    ->replaceArgument(11, null)
                    ->replaceArgument(12, null)
                ;
            }
        }
    }
}
