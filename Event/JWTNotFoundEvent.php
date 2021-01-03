<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

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
    public function __construct(AuthenticationException $exception = null, Response $response = null)
    {
        $this->exception = $exception;
        $this->response = $response;
    }
}
