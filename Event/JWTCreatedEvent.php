<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * JWTCreatedEvent.
 */
class JWTCreatedEvent extends Event
{
    /**
     * @var array
     */
    protected $header;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var UserInterface
     */
    protected $user;

    public function __construct(array $data, UserInterface $user, array $header = [])
    {
        $this->data = $data;
        $this->user = $user;
        $this->header = $header;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
