<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTCreator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

/**
 * AuthenticationSuccessHandler
 *
 * @author Dev Lexik <dev@lexik.fr>
 */
class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    /**
     * @var JWTCreator
     */
    protected $jwtCreator;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param JWTCreator $jwtCreator
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(JWTCreator $jwtCreator, EventDispatcherInterface $dispatcher)
    {
        $this->jwtCreator = $jwtCreator;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        $jwt = $this->jwtCreator->create($user);

        $event = new AuthenticationSuccessEvent(array('token' => $jwt), $user);
        $this->dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);

        return new JsonResponse($event->getData());
    }
}
