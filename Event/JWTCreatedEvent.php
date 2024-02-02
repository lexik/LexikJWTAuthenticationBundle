<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * JWTCreatedEvent.
 */
class JWTCreatedEvent extends Event
{
    protected array $header;
    protected array $data;
    protected UserInterface $user;

    public function __construct(array $data, UserInterface $user, array $header = [])
    {
        $this->data = $data;
        $this->user = $user;
        $this->header = $header;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
