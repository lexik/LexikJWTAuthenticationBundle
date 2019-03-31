<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    /**
     * @param array          $payload
     * @param TokenInterface $token
     */
    public function __construct(array $payload, TokenInterface $token)
    {
        $this->payload = $payload;
        $this->token   = $token;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
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
