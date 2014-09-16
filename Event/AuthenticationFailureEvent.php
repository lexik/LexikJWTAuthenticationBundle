<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

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
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
