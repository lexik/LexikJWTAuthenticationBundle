<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.security.jwt_authenticator', JWTAuthenticator::class)
        ->abstract()
        ->args([
            service('lexik_jwt_authentication.jwt_manager'),
            service('event_dispatcher'),
            service('lexik_jwt_authentication.extractor.chain_extractor'),
            '', // User Provider
            service('translator')->nullOnInvalid(),
        ]);
};
