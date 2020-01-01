<?php

namespace Mild\Support\Facades;

use Mild\Contract\Mail\TransportInterface;
use Mild\Contract\Event\EventDispatcherInterface;

/**
 * Class Mail
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Mail\Mailer
 * @method static void send($callable)
 * @method static TransportInterface getTransport()
 * @method static EventDispatcherInterface getEventDispatcher()
 */
class Mail extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'mail';
    }
}