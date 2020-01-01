<?php

namespace Mild\Contract\Event;

use Mild\Contract\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @return ListenerProviderInterface
     */
    public function getListenerProvider();
}