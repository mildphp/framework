<?php

namespace Mild\Contract\Mail;

use Mild\Contract\Event\EventDispatcherInterface;

interface MailerInterface
{
    /**
     * @param callable $callable
     * @return void
     */
    public function send(callable $callable);

    /**
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}