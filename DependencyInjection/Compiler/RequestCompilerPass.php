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

        $definition = $container->getDefinition('lexik_jwt_authentication.jwt_manager');

        if ($container->hasDefinition('request_stack')) {
            $definition->addMethodCall(
                'setRequest',
                [new Reference('request_stack', ContainerInterface::NULL_ON_INVALID_REFERENCE, false), false]
            );
        } else {
            $definition->addMethodCall(
                'setRequest',
                [new Reference('request', ContainerInterface::NULL_ON_INVALID_REFERENCE, false), false]
            );
        }
    }
}
