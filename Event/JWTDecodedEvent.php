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
     *
     * @deprecated since 1.7, removed in 2.0
     */
    protected $request;

    /**
     * @var bool
     */
    protected $isValid;

    /**
     * @param array        $payload
     * @param Request|null $request Deprecated
     */
    public function __construct(array $payload, Request $request = null)
    {
        if (null !== $request && class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Passing a Request instance as first argument of %s() is deprecated since version 1.7 and will be removed in 2.0.%sInject the "@request_stack" service in your event listener instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);

            $this->request = $request;
        }

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
     * @deprecated since 1.7, removed in 2.0
     *
     * @return Request
     */
    public function getRequest()
    {
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            @trigger_error(sprintf('Method %s() is deprecated since version 1.7 and will be removed in 2.0.%sUse  Symfony\Component\HttpFoundation\RequestStack::getCurrentRequest() instead.', __METHOD__, PHP_EOL), E_USER_DEPRECATED);
        }

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
