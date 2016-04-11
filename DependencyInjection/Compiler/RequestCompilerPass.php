<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * RequestCompilerPass.
 */
final class RequestCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('lexik_jwt_authentication.jwt_manager')) {
            return;
        }

        $serviceName = $container->hasDefinition('request_stack') ? 'request_stack' : 'request';

        $definition = $container->getDefinition('lexik_jwt_authentication.jwt_manager');
        $definition->addMethodCall(
            'setRequest',
            [new Reference($serviceName, ContainerInterface::NULL_ON_INVALID_REFERENCE)]
        );
    }
}
