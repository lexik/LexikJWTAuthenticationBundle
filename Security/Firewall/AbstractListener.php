<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;


if (interface_exists(ListenerInterface::class)) {
    if (class_exists(RequestEvent::class)) {
        class ForwardRequestEvent extends RequestEvent
        {
            private $event;

            public function __construct(GetResponseEvent $event)
            {
                parent::__construct($event->getKernel(), $event->getRequest(), $event->getRequestType());
                $this->event = $event;
            }

            public function getResponse()
            {
                return $this->event->getResponse();
            }

            public function setResponse(Response $response)
            {
                $this->event->setResponse($response);
            }

            public function hasResponse()
            {
                return $this->event->hasResponse();
            }
        }
    }

    /**
     * @internal
     */
    abstract class AbstractListener implements ListenerInterface
    {
        public function handle(GetResponseEvent $event)
        {
            if (class_exists(RequestEvent::class) && !$event instanceof RequestEvent) {
                $event = new ForwardRequestEvent($event);
            }

            $this->__invoke($event);
        }

        abstract public function __invoke($event);
    }
} else {
    abstract class AbstractListener
    {
        abstract public function __invoke($event);
    }
}
