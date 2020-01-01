<?php

namespace Mild\Event;

use Mild\Contract\Event\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array
     */
    protected $eventListeners = [];

    /**
     * @return array
     */
    public function getEventListeners()
    {
        return $this->eventListeners;
    }

    /**
     * @param object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (isset($this->eventListeners[$class = get_class($event)])) {
            return $this->eventListeners[$class];
        }

        return [];
    }

    /**
     * @param $event
     * @param callable $listener
     * @return void
     */
    public function addEventListener($event, callable $listener)
    {
        $this->eventListeners[$event][] = $listener;
    }
}