<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Tests\Functional\Utils;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CallableEventSubscriber implements EventSubscriberInterface
{
    private static $listeners     = [];

    private static $eventClassMap = [
        Events::JWT_CREATED       => JWTCreatedEvent::class,
        Events::JWT_DECODED       => JWTDecodedEvent::class,
        Events::JWT_INVALID       => JWTInvalidEvent::class,
        Events::JWT_NOT_FOUND     => JWTNotFoundEvent::class,
        Events::JWT_ENCODED       => JWTEncodedEvent::class,
        Events::JWT_AUTHENTICATED => JWTAuthenticatedEvent::class,
        Events::JWT_EXPIRED       => JWTExpiredEvent::class,
    ];

    public static function getSubscribedEvents()
    {
        $subscriberMap = [];

        foreach (self::$eventClassMap as $name => $className) {
            $subscriberMap[$name] = 'handleEvent';
        }

        return $subscriberMap;
    }

    /**
     * Executes the good listener depending on the passed event.
     *
     * @param object $event An instance of one of the events
     *                     defined in {@link self::$eventClassMap}
     */
    public function handleEvent($event)
    {
        $eventName = array_search(get_class($event), self::$eventClassMap);

        if (!$eventName) {
            return;
        }

        $listener = self::getListener($eventName);

        if (!$listener) {
            return;
        }

        if ($listener instanceof \Closure) {
            return $listener($event);
        }

        call_user_func($listener, $event);
    }

    /**
     * Checks whether a listener is registered for this event.
     *
     * @param string $eventName
     *
     * @return bool
     */
    public static function hasListener($eventName)
    {
        return isset(self::$listeners[$eventName]);
    }

    /**
     * Gets the listener for this event.
     *
     * @param string $eventName The event for which to retrieve the listener
     *
     * @return callable
     */
    public static function getListener($eventName)
    {
        if (!self::hasListener($eventName)) {
            return;
        }

        return self::$listeners[$eventName];
    }

    /**
     * Set the listener to use for a given event.
     *
     * @param string   $eventName The event to listen on
     * @param callable $listener  The callback to be executed for this event
     */
    public static function setListener($eventName, callable $listener)
    {
        self::$listeners[$eventName] = $listener;
    }

    /**
     * Unset the listener for a given event.
     *
     * @param string $eventName The event for which to unset the listener
     */
    public static function unsetListener($eventName)
    {
        if (!self::hasListener($eventName)) {
            return;
        }

        unset(self::$listeners[$eventName]);
    }
}
