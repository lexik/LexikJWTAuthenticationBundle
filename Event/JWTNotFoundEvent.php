<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * JWTNotFoundEvent event is dispatched when a JWT cannot be found in a request
 * covered by a firewall secured via lexik_jwt.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTNotFoundEvent extends AuthenticationFailureEvent implements JWTFailureEventInterface
{
    /**
     * @param Request|null                 $request   Deprecated
     * @param AuthenticationException|null $exception
     * @param Response|null                $response
     */
    public function __construct(Request $request = null, AuthenticationException $exception = null, Response $response = null)
    {
        if (null !== $request && class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Passing a Request instance as first argument of %s() is deprecated since version 1.7 and will be removed in 2.0.%sInject the "@request_stack" service in your event listener instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);

            $this->request = $request;
        }

        $this->exception = $exception;
        $this->response  = $response;
    }
}
