<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\DefaultEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\DefaultJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\Autowired;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class AutowiringTest extends \PHPUnit_Framework_TestCase
{
    public function testAutowiring()
    {
        $container = $this->createContainerBuilder();
        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());

        (new YamlFileLoader($container, new FileLocator([__DIR__.'/../app/config'])))->load('config_default.yml');

        $container
            ->register('autowired', Autowired::class)
            ->setAutowired(true);

        $container->compile();

        $autowired = $container->get('autowired');

        $this->assertInstanceOf(JWTManager::class, $autowired->getJWTManager());
        $this->assertInstanceOf(DefaultEncoder::class, $autowired->getJWTEncoder());
        $this->assertInstanceOf(ChainTokenExtractor::class, $autowired->getTokenExtractor());
        $this->assertInstanceOf(DefaultJWSProvider::class, $autowired->getJWSProvider());
    }

    public function testAutowireConfiguredEncoderServiceForInterfaceTypeHint()
    {
        if (!method_exists(ContainerBuilder::class, 'fileExists')) {
            $this->markTestSkipped('Using the configured encoder for autowiring is supported using symfony 3.3+ only.');
        }

        $container = $this->createContainerBuilder();
        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());

        (new YamlFileLoader($container, new FileLocator([__DIR__.'/../app/config'])))->load('config_custom_encoder.yml');

        $container
            ->register('autowired', Autowired::class)
            ->setAutowired(true);

        $container->compile();

        $autowired = $container->get('autowired');

        $this->assertInstanceOf(DummyEncoder::class, $autowired->getJWTEncoder());
    }

    private static function createContainerBuilder()
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.bundles'          => ['FrameworkBundle' => FrameworkBundle::class, 'LexikJWTAuthenticationBundle' => LexikJWTAuthenticationBundle::class],
            'kernel.bundles_metadata' => [],
            'kernel.cache_dir'        => __DIR__,
            'kernel.debug'            => false,
            'kernel.environment'      => 'test',
            'kernel.name'             => 'kernel',
            'kernel.root_dir'         => __DIR__,
            'kernel.container_class'  => 'AutowiringTestContainer',
            'kernel.charset'          => 'utf8',
        ]));
    }
}

final class DummyEncoder extends DefaultEncoder
{
    public function __construct()
    {
    }
}
