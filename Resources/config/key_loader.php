<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Services\KeyLoader\RawKeyLoader;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.key_loader.abstract')
        ->private()
        ->abstract()
        ->args([
            '', // private key
            '', // public key
            '%lexik_jwt_authentication.pass_phrase%',
            [], // additional public keys
        ]);

    $services->set('lexik_jwt_authentication.key_loader.raw', RawKeyLoader::class)
        ->parent('lexik_jwt_authentication.key_loader.abstract');
};
