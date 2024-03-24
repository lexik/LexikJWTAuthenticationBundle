<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\Clock\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AdditionalAccessTokenClaimsAndHeaderSubscriber implements EventSubscriberInterface
{
    /**
     * @var int|null
     */
    private $ttl;

    public function __construct(?int $ttl)
    {
        $this->ttl = $ttl;
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
        if (class_exists(Clock::class)) {
            $now = Clock::get()->now()->getTimestamp();
        } else {
            $now = time();
        }

        $claims = [
            'jti' => uniqid('', true),
            'iat' => $now,
            'nbf' => $now,
        ];
        $data = $event->getData();
        if (!array_key_exists('exp', $data) && $this->ttl > 0) {
            $claims['exp'] = $now + $this->ttl;
        }
        $event->setData(array_merge($claims, $data));
    }
}
