<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\LcobucciJWTEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\LcobucciJWSProvider;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.encoder.lcobucci', LcobucciJWTEncoder::class)
        ->args([service('lexik_jwt_authentication.jws_provider.lcobucci')]);

    $services->alias(JWSProviderInterface::class, 'lexik_jwt_authentication.jws_provider.lcobucci');

    $services->set('lexik_jwt_authentication.jws_provider.lcobucci', LcobucciJWSProvider::class)
        ->private()
        ->args([
            service('lexik_jwt_authentication.key_loader.raw'),
            '%lexik_jwt_authentication.encoder.signature_algorithm%',
            '%lexik_jwt_authentication.token_ttl%',
            '%lexik_jwt_authentication.clock_skew%',
            '%lexik_jwt_authentication.allow_no_expiration%',
        ]);
};
