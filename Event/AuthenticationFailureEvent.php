<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AuthenticationFailureEvent
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class AuthenticationFailureEvent extends Event
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var AuthenticationException
     */
    protected $exception;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     * @param Response                $response
     */
    public function __construct(Request $request, AuthenticationException $exception, Response $response)
    {
        $this->request = $request;
        $this->exception = $exception;
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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
}
