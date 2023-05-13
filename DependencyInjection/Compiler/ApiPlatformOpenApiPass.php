<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ApiPlatformOpenApiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('lexik_jwt_authentication.api_platform.openapi.factory') || !$container->hasParameter('security.firewalls')) {
            return;
        }

        $checkPath = null;
        $usernamePath = null;
        $passwordPath = null;
        $firewalls = $container->getParameter('security.firewalls');
        foreach ($firewalls as $firewallName) {
            if ($container->hasDefinition('security.authenticator.json_login.' . $firewallName)) {
                $firewallOptions = $container->getDefinition('security.authenticator.json_login.' . $firewallName)->getArgument(4);
                $checkPath = $firewallOptions['check_path'];
                $usernamePath = $firewallOptions['username_path'];
                $passwordPath = $firewallOptions['password_path'];

                break;
            }
        }

        $openApiFactoryDefinition = $container->getDefinition('lexik_jwt_authentication.api_platform.openapi.factory');
        $checkPathArg = $openApiFactoryDefinition->getArgument(1);
        $usernamePathArg = $openApiFactoryDefinition->getArgument(2);
        $passwordPathArg = $openApiFactoryDefinition->getArgument(3);

        if (!$checkPath && !$checkPathArg) {
            $container->removeDefinition('lexik_jwt_authentication.api_platform.openapi.factory');

            return;
        }

        if (!$checkPathArg) {
            $openApiFactoryDefinition->replaceArgument(1, $checkPath);
        }
        if (!$usernamePathArg) {
            $openApiFactoryDefinition->replaceArgument(2, $usernamePath ?? 'username');
        }
        if (!$passwordPathArg) {
            $openApiFactoryDefinition->replaceArgument(3, $passwordPath ?? 'password');
        }
    }
}
