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
 * @author Robin Chalas   <robin.chalas@gmail.com>
 */
class AuthenticationFailureEvent extends Event
{
    /**
     * @var Request
     *
     * @deprecated since 1.7, removed in 2.0
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
     * @param Request|null            $request   Deprecated
     * @param AuthenticationException $exception
     * @param Response                $response
     */
    public function __construct(Request $request = null, AuthenticationException $exception, Response $response)
    {
        if (null !== $request && class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Passing a Request instance as first argument of %s() is deprecated since version 1.7 and will be removed in 2.0.%sInject the "@request_stack" service in your event listener instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);

            $this->request = $request;
        }

        $this->exception = $exception;
        $this->response  = $response;
    }

    /**
     * @deprecated since 1.7, removed in 2.0
     *
     * @return Request
     */
    public function getRequest()
    {
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Method %s() is deprecated since version 1.7 and will be removed in 2.0.%sUse  Symfony\Component\HttpFoundation\RequestStack::getCurrentRequest() instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);
        }

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

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}
