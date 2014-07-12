<?php


namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTCreatedEvent
 */
class JWTCreatedEvent extends Event
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param array         $data
     * @param UserInterface $user
     * @param Request       $request
     */
    public function __construct(array $data, UserInterface $user, Request $request = null)
    {
        $this->data    = $data;
        $this->user    = $user;
        $this->request = $request;
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
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
