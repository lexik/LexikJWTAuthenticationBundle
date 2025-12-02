<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\WebTokenEncoder;
use Lexik\Bundle\JWTAuthenticationBundle\Subscriber\AdditionalAccessTokenClaimsAndHeaderSubscriber;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.encoder.web_token', WebTokenEncoder::class)
        ->private()
        ->args([
            service('lexik_jwt_authentication.access_token_builder')->nullOnInvalid(),
            service('lexik_jwt_authentication.access_token_loader')->nullOnInvalid(),
        ]);

    $services->set('lexik_jwt_authentication.subscriber.access_token_time', AdditionalAccessTokenClaimsAndHeaderSubscriber::class)
        ->private()
        ->args(['%lexik_jwt_authentication.token_ttl%'])
        ->tag('kernel.event_subscriber');
};
