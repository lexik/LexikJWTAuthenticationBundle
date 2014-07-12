<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

/**
 * JWTDecodedEvent
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
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @param array   $payload
     * @param Request $request
     */
    public function __construct(array $payload, Request $request = null)
    {
        $this->payload = $payload;
        $this->request = $request;
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
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Mark payload as invalid
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
