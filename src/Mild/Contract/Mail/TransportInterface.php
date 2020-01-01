<?php

namespace Mild\Contract\Mail;

use Mild\Contract\Event\EventDispatcherInterface;

interface TransportInterface
{
    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message);

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
}