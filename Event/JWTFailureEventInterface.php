<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Interface for event classes that are dispatched when a JWT cannot be authenticated.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
interface JWTFailureEventInterface
{
    /**
     * @return Response
     */
    public function getResponse();

    /**
     * @deprecated since 1.7, removed in 2.0
     *
     * @return Request
     */
    public function getRequest();

    /**
     * @return AuthenticationException
     */
    public function getException();

    /**
     * @param Response $response
     */
    public function setResponse(Response $response);
}
