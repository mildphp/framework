<?php

namespace Mild\Support\Facades;

use Mild\Contract\Event\EventInterface;
use Mild\Contract\Container\ContainerInterface;
use Mild\Contract\Event\ListenerProviderInterface;

/**
 * Class Event
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Event\EventDispatcher
 * @method static EventInterface dispatch($event)
 * @method static void listen($event, $listeners)
 * @method static ContainerInterface getContainer()
 * @method static ListenerProviderInterface getListenerProvider()
 */
class Event extends Facade
{
    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'event';
    }
}