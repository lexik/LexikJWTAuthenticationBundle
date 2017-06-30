<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * JWTDecodedEvent.
 *
 * @author Nicolas Cabot <n.cabot@lexik.fr>
 */
class JWTDecodedEvent extends Event
{
    /**
     * @var array
     */
    protected $payload;

    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
        $this->isValid = true;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Mark payload as invalid.
     */
    public function markAsInvalid()
    {
        $this->isValid = false;
        $this->stopPropagation();
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }
}
