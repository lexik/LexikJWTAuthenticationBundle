<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\HeaderAwareJWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Provides convenient methods to manage JWT creation/verification.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
class JWTManager implements JWTManagerInterface, JWTTokenManagerInterface
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
     * @var string
     */
    protected $userIdentityField;

    /**
     * @var string
     */
    protected $userIdClaim;

    /**
     * @param string|null $userIdClaim
     */
    public function __construct(JWTEncoderInterface $encoder, EventDispatcherInterface $dispatcher, $userIdClaim = null)
    {
        $this->jwtEncoder = $encoder;
        $this->dispatcher = $dispatcher;
        $this->userIdentityField = 'username';
        $this->userIdClaim = $userIdClaim;
    }

    /**
     * @return string The JWT token
     */
    public function create(UserInterface $user): string
    {
        $payload = ['roles' => $user->getRoles()];
        $this->addUserIdentityToPayload($user, $payload);

        return $this->generateJwtStringAndDispatchEvents($user, $payload);
    }

    /**
     * @return string The JWT token
     */
    public function createFromPayload(UserInterface $user, array $payload): string
    {
        $payload = array_merge(['roles' => $user->getRoles()], $payload);
        $this->addUserIdentityToPayload($user, $payload);

        return $this->generateJwtStringAndDispatchEvents($user, $payload);
    }

    /**
     * @return string The JWT token
     */
    private function generateJwtStringAndDispatchEvents(UserInterface $user, array $payload): string
    {
        $jwtCreatedEvent = new JWTCreatedEvent($payload, $user);
        $this->dispatcher->dispatch($jwtCreatedEvent, Events::JWT_CREATED);

        if ($this->jwtEncoder instanceof HeaderAwareJWTEncoderInterface) {
            $jwtString = $this->jwtEncoder->encode($jwtCreatedEvent->getData(), $jwtCreatedEvent->getHeader());
        } else {
            $jwtString = $this->jwtEncoder->encode($jwtCreatedEvent->getData());
        }

        $jwtEncodedEvent = new JWTEncodedEvent($jwtString);

        $this->dispatcher->dispatch($jwtEncodedEvent, Events::JWT_ENCODED);

        return $jwtString;
    }

    /**
     * {@inheritdoc}
     * @throws JWTDecodeFailureException
     */
    public function decode(TokenInterface $token)
    {
        if (!($payload = $this->jwtEncoder->decode($token->getCredentials()))) {
            return false;
        }

        $event = new JWTDecodedEvent($payload);
        $this->dispatcher->dispatch($event, Events::JWT_DECODED);

        if (!$event->isValid()) {
            return false;
        }

        return $event->getPayload();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $jwtToken): array
    {
        $payload = $this->jwtEncoder->decode($jwtToken);

        $event = new JWTDecodedEvent($payload);
        $this->dispatcher->dispatch($event, Events::JWT_DECODED);

        if (!$event->isValid()) {
            throw new JWTDecodeFailureException(JWTDecodeFailureException::INVALID_TOKEN, 'The token was marked as invalid by an event listener after successful decoding.');
        }

        return $event->getPayload();
    }

    /**
     * Add user identity to payload, username by default.
     * Override this if you need to identify it by another property.
     *
     * @param array &$payload
     */
    protected function addUserIdentityToPayload(UserInterface $user, array &$payload)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        if ($user instanceof InMemoryUser && ('username' === $this->userIdClaim || 'username' === $this->userIdentityField)) {
            $payload[$this->userIdClaim ?: $this->userIdentityField] = $accessor->getValue($user, 'userIdentifier');

            return;
        }

        $payload[$this->userIdClaim ?: $this->userIdentityField] = $accessor->getValue($user, $this->userIdentityField);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentityField(): string
    {
        return $this->userIdentityField;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserIdentityField($field)
    {
        $this->userIdentityField = $field;
    }

    /**
     * @return string
     */
    public function getUserIdClaim(): ?string
    {
        return $this->userIdClaim;
    }
}
