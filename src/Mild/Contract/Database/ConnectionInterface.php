<?php

namespace Mild\Contract\Database;

use PDO;
use PDOStatement;
use Mild\Contract\Event\EventInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Mild\Contract\Database\Query\CompilerInterface;

interface ConnectionInterface
{
    /**
     * @param $query
     * @param array $bindings
     * @return PDOStatement
     */
    public function execute($query, array $bindings = []);

    /**
     * @param callable $callable
     * @return mixed
     */
    public function transaction(callable $callable);

    /**
     * @return PDO
     */
    public function getPdo();

    /**
     * @return string
     */
    public function getDriverName();

    /**
     * @return CompilerInterface
     */
    public function getCompiler();

    /**
     * @param $event
     * @return void
     */
    public function dispatchEvent(EventInterface $event);

    /**
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher();

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher);
}