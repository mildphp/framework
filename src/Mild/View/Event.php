<?php

namespace Mild\View;

use Mild\Event\Event as BaseEvent;

class Event extends BaseEvent
{
    /**
     * @var Engine
     */
    public $engine;

    /**
     * Event constructor.
     *
     * @param Engine $engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }
}