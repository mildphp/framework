<?php

namespace Mild\Database\Events;

use Mild\Event\Event;
use Mild\Contract\Database\ConnectionInterface;

class QueryExecuted extends Event
{
    /**
     * @var string
     */
    public $sql;
    /**
     * @var int
     */
    public $time;
    /**
     * @var array
     */
    public $bindings;
    /**
     * @var ConnectionInterface
     */
    public $connection;

    /**
     * QueryExecuted constructor.
     *
     * @param ConnectionInterface $connection
     * @param $sql
     * @param $bindings
     * @param $time
     */
    public function __construct(ConnectionInterface $connection, $sql, $bindings, $time)
    {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->time = $time;
    }
}