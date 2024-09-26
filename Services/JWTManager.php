<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\HeaderAwareJWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\PayloadEnrichment\NullEnrichment;
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
class JWTManager implements JWTTokenManagerInterface
{
    protected JWTEncoderInterface $jwtEncoder;
    protected EventDispatcherInterface $dispatcher;
    protected string $userIdClaim;
    private $payloadEnrichment;

    public function __construct(JWTEncoderInterface $encoder, EventDispatcherInterface $dispatcher, string $userIdClaim, ?PayloadEnrichmentInterface $payloadEnrichment = null)
    {
        $this->jwtEncoder = $encoder;
        $this->dispatcher = $dispatcher;
        $this->userIdClaim = $userIdClaim;
        $this->payloadEnrichment = $payloadEnrichment ?? new NullEnrichment();
    }

    /**
     * @return string The JWT token
     *
     * @throws JWTEncodeFailureException
     */
    public function create(UserInterface $user): string
    {
        $payload = ['roles' => $user->getRoles()];
        $this->addUserIdentityToPayload($user, $payload);

        $this->payloadEnrichment->enrich($user, $payload);

        return $this->generateJwtStringAndDispatchEvents($user, $payload);
    }

    /**
     * @return string The JWT token
     *
     * @throws JWTEncodeFailureException
     */
    public function createFromPayload(UserInterface $user, array $payload = []): string
    {
        $payload = array_merge(['roles' => $user->getRoles()], $payload);
        $this->addUserIdentityToPayload($user, $payload);

        $this->payloadEnrichment->enrich($user, $payload);

        return $this->generateJwtStringAndDispatchEvents($user, $payload);
    }

    /**
     * @return string The JWT token
     *
     * @throws JWTEncodeFailureException
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
     *
     * @throws JWTDecodeFailureException
     */
    public function decode(TokenInterface $token): array|bool
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
     *
     * @throws JWTDecodeFailureException
     */
    public function parse(string $token): array
    {
        $payload = $this->jwtEncoder->decode($token);

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
     */
    protected function addUserIdentityToPayload(UserInterface $user, array &$payload): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $payload[$this->userIdClaim] = $accessor->getValue($user, $accessor->isReadable($user, $this->userIdClaim) ? $this->userIdClaim : 'user_identifier');
    }

    public function getUserIdClaim(): string
    {
        return $this->userIdClaim;
    }
}
