<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Clock\NativeClock;

final class AdditionalAccessTokenClaimsAndHeaderSubscriber implements EventSubscriberInterface
{
    private ?int $ttl;
    private ?ClockInterface $clock;

    public function __construct(?int $ttl, ?ClockInterface $clock = null)
    {
        $this->ttl = $ttl;

        if (null === $clock) {
            $this->clock = new NativeClock(new \DateTimeZone('UTC'));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::JWT_CREATED => [
                ['addClaims'],
            ],
        ];
    }

    public function addClaims(JWTCreatedEvent $event): void
    {
        $now = $this->clock->now();

        $claims = [
            'jti' => uniqid('', true),
            'iat' => $now,
            'nbf' => $now,
        ];
        $data = $event->getData();
        if (!array_key_exists('exp', $data) && $this->ttl > 0) {
            $claims['exp'] = $now->modify(sprintf('+%d second',$this->ttl));
        }
        $event->setData(array_merge($claims, $data));
    }
}
