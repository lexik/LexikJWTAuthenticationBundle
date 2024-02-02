<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * JWTAuthenticatedEvent.
 */
class JWTAuthenticatedEvent extends Event
{
    protected array $payload;
    protected TokenInterface $token;

    public function __construct(array $payload, TokenInterface $token)
    {
        $this->payload = $payload;
        $this->token = $token;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    public function getToken(): TokenInterface
    {
        return $this->token;
    }
}
