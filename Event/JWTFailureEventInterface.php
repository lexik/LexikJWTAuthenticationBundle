<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

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
     * Gets the response that will be returned after dispatching a
     * {@link JWTFailureEventInterface} implementation.
     *
     * @return Response
     */
    public function getResponse();

    /**
     * Gets the tied AuthenticationException object.
     *
     * @return AuthenticationException
     */
    public function getException();

    /**
     * Calling this allows to return a custom Response immediately after
     * the corresponding implementation of this event is dispatched.
     */
    public function setResponse(Response $response);
}
