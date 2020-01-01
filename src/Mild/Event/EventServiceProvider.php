<?php

namespace Mild\Event;

use Mild\Support\ServiceProvider;
use Mild\Contract\Event\EventDispatcherInterface;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('event', function ($app) {
            return new EventDispatcher($app, new ListenerProvider);
        });

        $this->application->alias(EventDispatcher::class, 'event');
        $this->application->alias(EventDispatcherInterface::class, 'event');
    }

    /**
     * @return void
     */
    public function boot()
    {
        /**
         * @var EventDispatcher $dispatcher
         */
        $dispatcher = $this->application->get(EventDispatcherInterface::class);

        Event::macro('listen', function ($listener) use ($dispatcher) {
            $dispatcher->listen(static::class, $listener);
        });
    }
}