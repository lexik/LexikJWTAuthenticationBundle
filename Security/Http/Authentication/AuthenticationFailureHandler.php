<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var TranslatorInterface|null
     */
    private $translator;

    public function __construct(EventDispatcherInterface $dispatcher, TranslatorInterface $translator = null)
    {
        $this->dispatcher = $dispatcher;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception->getPrevious() instanceof DisabledException) {
            $exception = $exception->getPrevious();
        }
        
        $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
        $statusCode = self::mapExceptionCodeToStatusCode($exception->getCode());
        if ($this->translator) {
            $errorMessage = $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security');
        }

        $event = new AuthenticationFailureEvent(
            $exception,
            new JWTAuthenticationFailureResponse($errorMessage, $statusCode),
            $request
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
