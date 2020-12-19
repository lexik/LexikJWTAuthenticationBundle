<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * AuthenticationFailureHandler.
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $event = new AuthenticationFailureEvent(
            $exception,
            new JWTAuthenticationFailureResponse($exception->getMessageKey())
        );

        $this->dispatcher->dispatch($event, Events::AUTHENTICATION_FAILURE);

        return $event->getResponse();
    }
}
