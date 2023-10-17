<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingClaimException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\BlockedTokenManager;

class RejectBlockedTokenListener
{
    public function __construct(private BlockedTokenManager $tokenManager)
    {
    }

    /**
     * @throws InvalidTokenException if the JWT is blocked
     */
    public function __invoke(JWTAuthenticatedEvent $event): void
    {
        try {
            if ($this->tokenManager->has($event->getPayload())) {
                throw new InvalidTokenException('JWT blocked');
            }
        } catch (MissingClaimException) {
            // Do nothing if the required claims do not exist on the payload (older JWTs won't have the "jti" claim the manager requires)
        }
    }
}
