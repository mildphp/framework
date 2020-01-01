<?php

namespace Mild\Database\Events;

use PDOStatement;
use Mild\Event\Event;
use Mild\Contract\Database\ConnectionInterface;

class StatementPrepared extends Event
{
    /**
     * @var ConnectionInterface
     */
    public $connection;
    /**
     * @var PDOStatement
     */
    public $statement;

    /**
     * StatementPrepared constructor.
     *
     * @param ConnectionInterface $connection
     * @param PDOStatement $statement
     */
    public function __construct(ConnectionInterface $connection, PDOStatement $statement)
    {
        $this->connection = $connection;
        $this->statement = $statement;
    }
}