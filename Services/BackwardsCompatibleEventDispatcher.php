<?php

namespace Lexik\Bundle\JWTAuthenticationBundle\Services;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

final class BackwardsCompatibleEventDispatcher
{
    /**
     * @var ContractsEventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param ContractsEventDispatcherInterface $dispatcher
     */
    private function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ContractsEventDispatcherInterface $dispatcher
     *
     * @return $this
     */
    public static function create($dispatcher)
    {
        return new self($dispatcher);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($event, $eventName = null)
    {
        if (!($this->dispatcher instanceof ContractsEventDispatcherInterface)) {
            return $this->dispatchToLegacyEventDispatcher($eventName, $event);
        }

        return $this->dispatcher->dispatch($event, $eventName);
    }

    /**
     * @deprecated This is a backward-compatibility layer for Symfony<4.
     *
     * @param string $eventName
     * @param object $event
     *
     * @return object
     */
    private function dispatchToLegacyEventDispatcher($eventName, $event)
    {
        return $this->dispatcher->dispatch($eventName, $event);
    }
}
