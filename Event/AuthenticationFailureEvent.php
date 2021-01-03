<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * AuthenticationFailureEvent.
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 * @author Robin Chalas   <robin.chalas@gmail.com>
 */
class AuthenticationFailureEvent extends Event
{
    /**
     * @var AuthenticationException
     */
    protected $exception;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(AuthenticationException $exception, Response $response)
    {
        $this->exception = $exception;
        $this->response = $response;
    }

    /**
     * @return AuthenticationException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
