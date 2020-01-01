<?php

namespace Mild\Support\Traits;

use Mild\Database\Connection;
use Mild\Database\Query\Builder;

trait DatabaseHandlerTrait
{
    /**
     * @var string
     */
    private $table;
    /**
     * @var array
     */
    private $columns;
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param $name
     * @return mixed
     */
    private function getColumn($name)
    {
        return $this->columns[$name] ?? $name;
    }

    /**
     * @return Builder
     */
    private function createQuery()
    {
        return $this->connection->table($this->table);
    }
}