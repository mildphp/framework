<?php

namespace Mild\Database\Events;

use Mild\Event\Event;
use Mild\Contract\Database\ConnectionInterface;

abstract class Transaction extends Event
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Transaction constructor.
     *
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }
}