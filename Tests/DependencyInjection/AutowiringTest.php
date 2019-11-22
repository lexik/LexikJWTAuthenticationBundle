<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\DependencyInjection;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Tests\Stubs\Autowired;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\ChainTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class AutowiringTest extends TestCase
{
    public function testAutowiring()
    {
        $container = $this->createContainerBuilder([
            'framework'                => ['secret' => 'test'],
            'lexik_jwt_authentication' => [
                'secret_key'  => 'private.pem',
                'public_key'  => 'public.pem',
                'pass_phrase' => 'testing',
            ],
        ]);

        $container
            ->register('autowired', Autowired::class)
            ->setPublic(true)
            ->setAutowired(true);

        $container->compile();

        $autowired = $container->get('autowired');

        $this->assertInstanceOf(JWTManager::class, $autowired->getJWTManager());
        $this->assertInstanceOf(LcobucciJWTEncoder::class, $autowired->getJWTEncoder());
        $this->assertInstanceOf(ChainTokenExtractor::class, $autowired->getTokenExtractor());
        $this->assertInstanceOf(LcobucciJWSProvider::class, $autowired->getJWSProvider());
        $this->assertInstanceOf(AuthenticationSuccessHandler::class, $autowired->getAuthenticationSuccessHandler());
        $this->assertInstanceOf(AuthenticationFailureHandler::class, $autowired->getAuthenticationFailureHandler());
    }

    public function testAutowireConfiguredEncoderServiceForInterfaceTypeHint()
    {
        $container = $this->createContainerBuilder([
            'framework'                => ['secret' => 'testing'],
            'lexik_jwt_authentication' => [
                'secret_key'  => 'private.pem',
                'pass_phrase' => 'testing',
                'encoder'     => ['service' => 'app.dummy_encoder'],
            ],
        ]);

        $container
            ->register('app.dummy_encoder')
            ->setClass(DummyEncoder::class)
            ->setPublic(true);

        $container
            ->register('autowired', Autowired::class)
            ->setPublic(true)
            ->setAutowired(true);

        $container->compile();

        $autowired = $container->get('autowired');

        $this->assertInstanceOf(DummyEncoder::class, $autowired->getJWTEncoder());
    }

    private static function createContainerBuilder(array $configs = [])
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.bundles'          => ['FrameworkBundle' => FrameworkBundle::class, 'LexikJWTAuthenticationBundle' => LexikJWTAuthenticationBundle::class],
            'kernel.bundles_metadata' => [],
            'kernel.cache_dir'        => __DIR__,
            'kernel.debug'            => false,
            'kernel.environment'      => 'test',
            'kernel.name'             => 'kernel',
            'kernel.root_dir'         => __DIR__,
            'kernel.project_dir'      => __DIR__,
            'kernel.container_class'  => 'AutowiringTestContainer',
            'kernel.charset'          => 'utf8',
            'env(base64:default::SYMFONY_DECRYPTION_SECRET)' => 'dummy',
        ]));

        $container->registerExtension(new SecurityExtension());
        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new LexikJWTAuthenticationExtension());

        foreach ($configs as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }

        return $container;
    }
}

final class DummyEncoder extends LcobucciJWTEncoder
{
    public function __construct()
    {
    }
}
