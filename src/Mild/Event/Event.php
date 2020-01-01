<?php

namespace Mild\Event;

use Mild\Support\Traits\Macroable;
use Mild\Contract\Event\EventInterface;

/**
 * Class Event
 *
 * @package Mild\Event
 * @method static void listen($listener)
 */
abstract class Event implements EventInterface
{
    use Macroable;

    /**
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * @return void
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}