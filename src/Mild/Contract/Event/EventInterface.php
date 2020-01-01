<?php

namespace Mild\Contract\Event;

use Psr\EventDispatcher\StoppableEventInterface;

interface EventInterface extends StoppableEventInterface
{
    /**
     * @return void
     */
    public function stopPropagation();
}