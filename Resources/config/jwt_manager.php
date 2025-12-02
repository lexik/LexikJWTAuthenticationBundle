<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\PayloadEnrichment\ChainEnrichment;
use Lexik\Bundle\JWTAuthenticationBundle\Services\PayloadEnrichment\RandomJtiEnrichment;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.jwt_manager', JWTManager::class)
        ->public()
        ->args([
            service('lexik_jwt_authentication.encoder'),
            service('event_dispatcher'),
            '%lexik_jwt_authentication.user_id_claim%',
            service('lexik_jwt_authentication.payload_enrichment'),
        ]);

    $services->alias(JWTTokenManagerInterface::class, 'lexik_jwt_authentication.jwt_manager');

    $services->set('lexik_jwt_authentication.payload_enrichment.random_jti_enrichment', RandomJtiEnrichment::class)
        ->tag('lexik_jwt_authentication.payload_enrichment', ['priority' => 0]);

    $services->set('lexik_jwt_authentication.payload_enrichment', ChainEnrichment::class)
        ->args([[]]);
};
