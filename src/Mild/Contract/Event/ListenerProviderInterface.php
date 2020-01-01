<?php

namespace Mild\Contract\Event;

use Psr\EventDispatcher\ListenerProviderInterface as BaseListenerProviderInterface;

interface ListenerProviderInterface extends BaseListenerProviderInterface
{
    /**
     * @return array
     */
    public function getEventListeners();

    /**
     * @param $event
     * @param callable $listener
     * @return void
     */
    public function addEventListener($event, callable $listener);
}