<?php

namespace Mild\Routing;

use Mild\Event\Event as BaseEvent;

class RouteEvent extends BaseEvent
{
    /**
     * @var Route
     */
    public $route;

    /**
     * Event constructor.
     *
     * @param $route
     */
    public function __construct($route)
    {
        $this->route = $route;
    }
}