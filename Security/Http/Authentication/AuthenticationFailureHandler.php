<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        $statusCode = self::mapExceptionCodeToStatusCode($exception->getCode());

        $event = new AuthenticationFailureEvent(
            $exception,
            new JWTAuthenticationFailureResponse($errorMessage, $statusCode)
        );

        $this->dispatcher->dispatch($event, Events::AUTHENTICATION_FAILURE);

        return $event->getResponse();
    }

    /**
     * @param string|int $exceptionCode
     */
    private static function mapExceptionCodeToStatusCode($exceptionCode): int
    {
        $canMapToStatusCode = is_int($exceptionCode)
            && $exceptionCode >= 400
            && $exceptionCode < 500;

        return $canMapToStatusCode
            ? $exceptionCode
            : Response::HTTP_UNAUTHORIZED;
    }
}
