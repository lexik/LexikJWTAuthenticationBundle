<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\DependencyInjection\Security\Factory;

use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\LexikJWTAuthenticationExtension;
use Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Security\Factory\JWTUserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JWTUserFactoryTest extends TestCase
{
    public function testCreatedProviderIsResetBetweenRequests()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new LexikJWTAuthenticationExtension());
        $container->loadFromExtension('lexik_jwt_authentication', ['secret_key' => 'private.pem', 'public_key' => 'public.pem']);

        (new JWTUserFactory())->create($container, 'security.user.provider.concrete.jwt', ['class' => JWTUser::class]);

        $container->getCompilerPassConfig()->setOptimizationPasses([new ResolveChildDefinitionsPass()]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);
        $container->compile();

        $this->assertSame(
            [['method' => 'reset']],
            $container->getDefinition('security.user.provider.concrete.jwt')->getTag('kernel.reset')
        );
    }
}
