<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class AddClaimsToJWTListener
{
    public function __invoke(JWTCreatedEvent $event): void
    {
        $data = $event->getData();

        if (!isset($data['jti'])) {
            $data['jti'] = bin2hex(random_bytes(16));

            $event->setData($data);
        }
    }
}
