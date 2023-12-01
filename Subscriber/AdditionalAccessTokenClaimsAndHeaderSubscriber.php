<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
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
        $claims = [
            'jti' => uniqid('', true),
            'iat' => time(),
            'nbf' => time(),
        ];
        $data = $event->getData();
        if (!array_key_exists('exp', $data) && $this->ttl > 0) {
            $claims['exp'] = time() + $this->ttl;
        }
        $event->setData(array_merge($claims, $data));
    }
}
