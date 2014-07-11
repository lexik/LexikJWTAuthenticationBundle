<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use RuntimeException;

class JWTCreator
{
    /**
     * @param JWTEncoder $encoder
     * @param EventDispatcherInterface $dispatcher
     * @param int $ttl
     */
    public function __construct(JWTEncoder $encoder, EventDispatcherInterface $dispatcher, $ttl)
    {
        $this->encoder    = $encoder;
        $this->dispatcher = $dispatcher;
        $this->ttl        = $ttl;
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    public function create(UserInterface $user)
    {
        $payload             = array();
        $payload['exp']      = time() + $this->ttl;
        $payload['username'] = $user->getUsername();

        $event = new JWTCreatedEvent($payload, $user);
        $this->dispatcher->dispatch(Events::JWT_CREATED, $event);

        $payload = $event->getData();
        $jwt = $this->encoder->encode($payload)->getTokenString();

        return $jwt;
    }
}
