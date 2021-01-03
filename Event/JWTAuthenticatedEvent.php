<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * JWTAuthenticatedEvent.
 */
class JWTAuthenticatedEvent extends Event
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var TokenInterface
     */
    protected $token;

    public function __construct(array $payload, TokenInterface $token)
    {
        $this->payload = $payload;
        $this->token = $token;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }
}
