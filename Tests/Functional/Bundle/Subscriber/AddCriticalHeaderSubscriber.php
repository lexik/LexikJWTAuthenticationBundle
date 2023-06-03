<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Bundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\BeforeJWEComputationEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddCriticalHeaderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::BEFORE_JWE_COMPUTATION => [
                ['addHeader'],
            ],
        ];
    }

    public function addHeader(BeforeJWEComputationEvent $event): void
    {
        $event->setHeader('crit', ['exp', 'iat', 'nbf']);
    }
}
