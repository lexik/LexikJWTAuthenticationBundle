<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * AuthenticationFailureHandler
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    const RESPONSE_CODE    = 401;
    const RESPONSE_MESSAGE = 'Bad credentials';

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
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'code'    => self::RESPONSE_CODE,
            'message' => self::RESPONSE_MESSAGE,
        );

        $response = new JsonResponse($data, self::RESPONSE_CODE);
        $event = new AuthenticationFailureEvent($request, $exception, $response);

        $this->dispatcher->dispatch(Events::AUTHENTICATION_FAILURE, $event);

        return $event->getResponse();
    }
}
