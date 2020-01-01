<?php

namespace Mild\Mail;

use Mild\Contract\Mail\TransportInterface;
use Mild\Contract\Event\EventDispatcherInterface;

abstract class AbstractTransport implements TransportInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param $event
     * @return void
     */
    protected function dispatchEvent($event)
    {
        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch($event);
        }
    }
}