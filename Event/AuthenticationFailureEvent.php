<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AuthenticationFailureEvent
 *
 * @author Emmanuel Vella <vella.emmanuel@gmail.com>
 */
class AuthenticationFailureEvent extends GetResponseEvent
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
     * @param Request $request
     */
    public function __construct(Request $request, AuthenticationException $exception)
    {
        $this->request = $request;
        $this->exception = $exception;
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
}
