<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * JWTManager
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTManager
{
    /**
     * @var JWTEncoderInterface
     */
    protected $jwtEncoder;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param JWTEncoderInterface      $encoder
     * @param EventDispatcherInterface $dispatcher
     * @param int                      $ttl
     */
    public function __construct(JWTEncoderInterface $encoder, EventDispatcherInterface $dispatcher, $ttl)
    {
        $this->jwtEncoder = $encoder;
        $this->dispatcher = $dispatcher;
        $this->ttl        = $ttl;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param UserInterface $user
     *
     * @return string
     */
    public function create(UserInterface $user)
    {
        $payload             = array();
        $payload['exp']      = time() + $this->ttl;
        $payload['username'] = $user->getUsername();

        $event = new JWTCreatedEvent($payload, $user, $this->request);
        $this->dispatcher->dispatch(Events::JWT_CREATED, $event);

        return $this->jwtEncoder->encode($event->getData());
    }

    /**
     * @param TokenInterface $token
     *
     * @return bool|string
     */
    public function decode(TokenInterface $token)
    {
        if (!($payload = $this->jwtEncoder->decode($token->getCredentials()))) {
            return false;
        }

        $event = new JWTDecodedEvent($payload, $this->request);
        $this->dispatcher->dispatch(Events::JWT_DECODED, $event);

        if (!$event->isValid()) {
            return false;
        }

        return $payload;
    }
}
