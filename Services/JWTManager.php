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
class JWTManager implements JWTManagerInterface
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
    protected $request;

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
     * {@inheritdoc}
     */
    public function create(UserInterface $user)
    {
        $payload = array(
            'exp' => time() + $this->ttl
        );

        $this->addUserIdentityToPayload($user, $payload);

        $event = new JWTCreatedEvent($payload, $user, $this->request);
        $this->dispatcher->dispatch(Events::JWT_CREATED, $event);

        return $this->jwtEncoder->encode($event->getData());
    }

    /**
     * Add user identity to payload, username by default.
     * Override this if you need to identify it by another property.
     *
     * @param UserInterface $user
     * @param array         $payload
     */
    protected function addUserIdentityToPayload(UserInterface $user, array &$payload)
    {
        $payload['username'] = $user->getUsername();
    }

    /**
     * {@inheritdoc}
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
