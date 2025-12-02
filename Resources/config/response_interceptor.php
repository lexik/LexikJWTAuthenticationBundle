<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.handler.authentication_success', AuthenticationSuccessHandler::class)
        ->args([
            service('lexik_jwt_authentication.jwt_manager'),
            service('event_dispatcher'),
            [], // Cookie providers
            true,
        ])
        ->tag('monolog.logger', ['channel' => 'security']);

    $services->alias(AuthenticationSuccessHandler::class, 'lexik_jwt_authentication.handler.authentication_success');

    $services->set('lexik_jwt_authentication.handler.authentication_failure', AuthenticationFailureHandler::class)
        ->args([
            service('event_dispatcher'),
            service('translator')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'security']);

    $services->alias(AuthenticationFailureHandler::class, 'lexik_jwt_authentication.handler.authentication_failure');
};
