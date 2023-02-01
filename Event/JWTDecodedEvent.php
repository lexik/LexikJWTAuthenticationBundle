<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * JWTDecodedEvent.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTDecodedEvent extends Event
{
    protected array $payload;

    protected bool $isValid;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->isValid = true;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Mark payload as invalid.
     */
    public function markAsInvalid(): void
    {
        $this->isValid = false;
        $this->stopPropagation();
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}
