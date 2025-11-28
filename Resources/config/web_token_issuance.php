<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Jose\Bundle\JoseFramework\Services\JWEBuilderFactory;
use Jose\Bundle\JoseFramework\Services\JWSBuilderFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\WebToken\AccessTokenBuilder;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.access_token_builder', AccessTokenBuilder::class)
        ->private()
        ->args([
            service(EventDispatcherInterface::class),
            service(JWSBuilderFactory::class),
            service(JWEBuilderFactory::class)->nullOnInvalid(),
            '', // Signature algorithm
            '', // Signature key
            null, // Key encryption algorithm
            null, // Content encryption algorithm
            null, // Encryption key
        ]);
};
