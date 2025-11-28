<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.cookie_provider', JWTCookieProvider::class)
        ->abstract()
        ->args([
            null, // Default name
            null, // Default lifetime
            '', // Default samesite
            '', // Default path
            null, // Default domain
            '', // Default secure
            '', // Default httpOnly
            null, // Default split
            false, // Default partitioned
        ]);
};
