<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Bundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle\Security;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * AppKernel.
 */
class AppKernel extends Kernel
{
    private $encoder;

    private $userProvider;

    private $signatureAlgorithm;

    private $testCase;

    public function __construct($environment, $debug, $testCase = null)
    {
        parent::__construct($environment, $debug);

        $this->testCase = $testCase;
        $this->encoder = getenv('ENCODER') ?: 'default';
        $this->userProvider = getenv('PROVIDER') ?: 'in_memory';
        $this->signatureAlgorithm = getenv('ALGORITHM');
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): array
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new LexikJWTAuthenticationBundle(),
            new Bundle(),
        ];
        if (class_exists(ApiPlatformBundle::class)) {
            $bundles[] = new ApiPlatformBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/LexikJWTAuthenticationBundle/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/LexikJWTAuthenticationBundle/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_router_utf8.yml');

        // 5.3+ session config
        if (class_exists(UserNotFoundException::class)) {
            $sessionConfig = [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ];
        } else {
            $sessionConfig = [
                'handler_id' => null,
                'cookie_secure' => 'auto',
                'cookie_samesite' => 'lax',
                'storage_id' => 'session.storage.mock_file',
            ];
        }

        if (!class_exists(Security::class)) {
            $loader->load(function (ContainerBuilder $container) {
                $container->prependExtensionConfig('security', [
                    'enable_authenticator_manager' => true,
                ]);
            });
        }

        $router = [
            'resource' => '%kernel.root_dir%/config/routing.yml',
            'utf8' => true,
        ];
        if (class_exists(ApiPlatformBundle::class)) {
            $loader->load(function (ContainerBuilder $container) use (&$router) {
                $container->prependExtensionConfig('api_platform', [
                    'title' => 'LexikJWTAuthenticationBundle',
                    'description' => 'API Platform integration in LexikJWTAuthenticationBundle',
                    'version' => '1.0.0',
                ]);
                $container->prependExtensionConfig('lexik_jwt_authentication', [
                    'api_platform' => [
                        'check_path' => '/login_check',
                    ],
                ]);
                $router['resource'] = '%kernel.root_dir%/config/routing_api_platform.yml';
            });
        }

        $loader->load(function (ContainerBuilder $container) use ($router, $sessionConfig) {
            $container->prependExtensionConfig('framework', [
                'router' => $router,
                'session' => $sessionConfig
            ]);
        });

        if ($this->testCase && file_exists(__DIR__ . '/config/' . $this->testCase . '/config.yml')) {
            $loader->load(__DIR__ . '/config/' . $this->testCase . '/config.yml');
        }

        $loader->load(__DIR__ . sprintf('/config/security_%s.yml', $this->userProvider . (class_exists(UserNotFoundException::class) ? '' : '_legacy')));

        if ($this->signatureAlgorithm && file_exists($file = __DIR__ . sprintf('/config/config_%s_%s.yml', $this->encoder, strtolower($this->signatureAlgorithm)))) {
            $loader->load($file);

            return;
        }

        $loader->load(__DIR__ . sprintf('/config/config_%s.yml', $this->encoder));
    }

    public function getUserProvider()
    {
        return $this->userProvider;
    }

    public function getEncoder()
    {
        return $this->encoder;
    }

    protected function build(ContainerBuilder $container)
    {
        $container->register('logger', NullLogger::class);

        if (!$container->hasParameter('kernel.root_dir')) {
            $container->setParameter('kernel.root_dir', $this->getRootDir());
        }
    }
}
