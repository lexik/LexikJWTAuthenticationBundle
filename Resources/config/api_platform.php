<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\OpenApi\OpenApiFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.api_platform.openapi.factory', OpenApiFactory::class)
        ->private()
        ->decorate('api_platform.openapi.factory', null, 0, ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        ->args([
            service('lexik_jwt_authentication.api_platform.openapi.factory.inner'),
            '', // check path
            '', // username path
            '', // password path
        ]);
};
