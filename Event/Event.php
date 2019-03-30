<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Contracts\EventDispatcher\Event as ContractsBaseEvent;

if (class_exists(ContractsBaseEvent::class)) {
    class Event extends ContractsBaseEvent
    {
    }
} else {
    class Event extends BaseEvent
    {
    }
}
