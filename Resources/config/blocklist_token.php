<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Lexik\Bundle\JWTAuthenticationBundle\EventListener\BlockJWTListener;
use Lexik\Bundle\JWTAuthenticationBundle\EventListener\RejectBlockedTokenListener;
use Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedToken\CacheItemPoolBlockedTokenManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedTokenManagerInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('lexik_jwt_authentication.event_listener.block_jwt_listener', BlockJWTListener::class)
        ->args([
            service('lexik_jwt_authentication.blocked_token_manager'),
            service('lexik_jwt_authentication.extractor.chain_extractor'),
            service('lexik_jwt_authentication.jwt_manager'),
        ])
        ->tag('kernel.event_listener', ['event' => LoginFailureEvent::class, 'method' => 'onLoginFailure', 'dispatcher' => 'event_dispatcher'])
        ->tag('kernel.event_listener', ['event' => LogoutEvent::class, 'method' => 'onLogout', 'dispatcher' => 'event_dispatcher']);

    $services->set('lexik_jwt_authentication.event_listener.reject_blocked_token_listener', RejectBlockedTokenListener::class)
        ->args([service('lexik_jwt_authentication.blocked_token_manager')])
        ->tag('kernel.event_listener', ['event' => 'lexik_jwt_authentication.on_jwt_authenticated']);

    $services->set('lexik_jwt_authentication.blocked_token_manager', CacheItemPoolBlockedTokenManager::class)
        ->args([service('lexik_jwt_authentication.blocklist_token.cache')]);

    $services->alias(BlockedTokenManagerInterface::class, 'lexik_jwt_authentication.blocked_token_manager');
};
