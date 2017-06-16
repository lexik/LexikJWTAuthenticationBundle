<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Security\Core\User\UserInterface;

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

    /**
     * @param array         $header
     * @param array         $data
     * @param UserInterface $user
     */
    public function __construct(array $header, array $data, UserInterface $user)
    {
        $this->header = $header;
        $this->data = $data;
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
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

    /**
     * @param array $data
     */
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
