<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Security\JWTEncoder;
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
     * @var JWTEncoder
     */
    protected $encoder;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @param JWTEncoder               $encoder
     * @param EventDispatcherInterface $dispatcher
     * @param int                      $ttl
     */
    public function __construct(JWTEncoder $encoder, EventDispatcherInterface $dispatcher, $ttl)
    {
        $this->encoder    = $encoder;
        $this->dispatcher = $dispatcher;
        $this->ttl        = $ttl;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        $payload             = array();
        $payload['exp']      = time() + $this->ttl;
        $payload['username'] = $user->getUsername();

        $jwt = $this->encoder->encode($payload)->getTokenString();

        $event = new AuthenticationSuccessEvent(array('token' => $jwt), $user);
        $this->dispatcher->dispatch(Events::AUTHENTICATION_SUCCESS, $event);

        return new JsonResponse($event->getData());
    }
}
