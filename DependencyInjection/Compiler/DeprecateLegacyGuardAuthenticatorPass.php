<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @internal
 */
class DeprecateLegacyGuardAuthenticatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('lexik_jwt_authentication.authenticator_manager_enabled') || !$container->getParameter('lexik_jwt_authentication.authenticator_manager_enabled')) {
            return;
        }

        $deprecationArgs = ['The "%service_id%" service is deprecated and will be removed in 3.0, use the new "jwt" authenticator instead.'];
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            $deprecationArgs = ['lexik/jwt-authentication-bundle', '2.7', 'The "%service_id%" service is deprecated and will be removed in 3.0, use the new "jwt" authenticator instead.'];
        }

        $container
            ->getDefinition('lexik_jwt_authentication.security.guard.jwt_token_authenticator')
            ->setDeprecated(...$deprecationArgs);
    }
}

