<?php

namespace Mild\Event;

use Mild\Support\Arr;
use InvalidArgumentException;
use Mild\Contract\Event\EventInterface;
use Mild\Contract\Container\ContainerInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Mild\Contract\Event\ListenerProviderInterface;

class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ListenerProviderInterface
     */
    protected $listenerProvider;

    /**
     * EventDispatcher constructor.
     *
     * @param ContainerInterface $container
     * @param ListenerProviderInterface $listenerProvider
     */
    public function __construct(ContainerInterface $container, ListenerProviderInterface $listenerProvider)
    {
        $this->container = $container;
        $this->listenerProvider = $listenerProvider;
    }

    /**
     * @param object $event
     * @return EventInterface
     */
    public function dispatch(object $event)
    {
        if ($event instanceof EventInterface === false) {
            throw new InvalidArgumentException(sprintf(
                'The event must be instanceof %s', EventInterface::class
            ));
        }

        /**
         * @var EventInterface $event
         */
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            if ($event->isPropagationStopped()) {
                continue;
            }
            $this->container->make($listener, [$event]);
        }

        return $event;
    }

    /**
     * @param $event
     * @param array|string $listeners
     * @return void
     */
    public function listen($event, $listeners)
    {
        foreach (Arr::wrap($listeners) as $listener) {
            if (!is_callable($listener)) {
                $listener = $this->container->make($listener);
            }
            $this->listenerProvider->addEventListener($event, $listener);
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return ListenerProviderInterface
     */
    public function getListenerProvider()
    {
        return $this->listenerProvider;
    }
}