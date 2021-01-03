<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * AuthenticationSuccessEvent.
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class AuthenticationSuccessEvent extends Event
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
     * @var Response
     */
    protected $response;

    public function __construct(array $data, UserInterface $user, Response $response)
    {
        $this->data = $data;
        $this->user = $user;
        $this->response = $response;
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

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
