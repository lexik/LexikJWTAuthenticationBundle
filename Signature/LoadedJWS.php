<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Signature;

/**
 * Object representation of a JSON Web Signature loaded from an
 * existing JSON Web Token.
 *
 * @author Robin Chalas <robin.chalas@gmail.com>
 */
final class LoadedJWS
{
    const VERIFIED = 'verified';
    const EXPIRED = 'expired';
    const INVALID = 'invalid';

    private $header;
    private $payload;
    private $state;
    private $clockSkew;
    private $hasLifetime;

    public function __construct(array $payload, bool $isVerified, bool $hasLifetime = true, array $header = [], int $clockSkew = 0)
    {
        $this->payload = $payload;
        $this->header = $header;
        $this->hasLifetime = $hasLifetime;
        $this->clockSkew = $clockSkew;

        if (true === $isVerified) {
            $this->state = self::VERIFIED;
        }

        $this->checkIssuedAt();
        $this->checkExpiration();
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function isVerified(): bool
    {
        return self::VERIFIED === $this->state;
    }

    public function isExpired(): bool
    {
        $this->checkExpiration();

        return self::EXPIRED === $this->state;
    }

    public function isInvalid(): bool
    {
        return self::INVALID === $this->state;
    }

    private function checkExpiration(): void
    {
        if (!$this->hasLifetime) {
            return;
        }

        if (!isset($this->payload['exp']) || !is_numeric($this->payload['exp'])) {
            $this->state = self::INVALID;

            return;
        }

        if ($this->clockSkew <= time() - $this->payload['exp']) {
            $this->state = self::EXPIRED;
        }
    }

    /**
     * Ensures that the iat claim is not in the future.
     */
    private function checkIssuedAt()
    {
        if (isset($this->payload['iat']) && (int) $this->payload['iat'] - $this->clockSkew > time()) {
            return $this->state = self::INVALID;
        }
    }
}
