<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\DependencyInjection\Compiler;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class RegisterLegacyGuardAuthenticatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('lexik_jwt_authentication.authenticator_manager_enabled') || !$container->getParameter('lexik_jwt_authentication.authenticator_manager_enabled')) {
            return;
        }

        $container->register('.lexik_jwt_authentication.pre_authentication_token_storage', TokenStorage::class);
        $container
            ->register('lexik_jwt_authentication.security.guard.jwt_token_authenticator', JWTTokenAuthenticator::class)
            ->setArguments([
                new Reference('lexik_jwt_authentication.jwt_manager'),
                new Reference('event_dispatcher'),
                new Reference('lexik_jwt_authentication.extractor.chain_extractor'),
                new Reference('.lexik_jwt_authentication.pre_authentication_token_storage'),
            ])
        ;
        $container->setAlias('lexik_jwt_authentication.jwt_token_authenticator', 'lexik_jwt_authentication.security.guard.jwt_token_authenticator');
        $container->getParameterBag()->remove('lexik_jwt_authentication.authenticator_manager_enabled');
    }
}

