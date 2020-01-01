<?php

namespace Mild\Database;

use PDO;
use Throwable;
use PDOStatement;
use PDOException;
use Mild\Database\Query\Builder;
use Mild\Database\Query\Expression;
use Mild\Database\Events\QueryExecuted;
use Mild\Contract\Event\EventInterface;
use Mild\Database\Events\StatementPrepared;
use Mild\Database\Exceptions\QueryException;
use Mild\Database\Events\TransactionBeginning;
use Mild\Database\Events\TransactionCommitted;
use Mild\Database\Events\TransactionRolledBack;
use Mild\Contract\Database\ConnectionInterface;
use Mild\Contract\Event\EventDispatcherInterface;
use Mild\Contract\Database\Query\CompilerInterface;

class Connection implements ConnectionInterface
{
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var CompilerInterface
     */
    protected $compiler;
    /**
     * @var string
     */
    protected $driverName;
    /**
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher;

    /**
     * Connection constructor.
     * @param PDO $pdo
     * @param CompilerInterface $compiler
     */
    public function __construct(PDO $pdo, CompilerInterface $compiler)
    {
        $this->pdo = $pdo;
        $this->compiler = $compiler;
        $this->driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    /**
     * @param $query
     * @param array $bindings
     * @return PDOStatement
     * @throws QueryException
     */
    public function execute($query, array $bindings = [])
    {
        $start = microtime(true);

        try {

            $this->dispatchEvent(new StatementPrepared(
                $this, $statement = $this->pdo->prepare($query)
            ));

            foreach ($bindings as $key => $value) {
                $statement->bindValue(
                    is_string($key) ? $key : $key + 1, $value,
                    is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }

            $statement->execute();
        } catch (PDOException $e) {
            throw new QueryException($query, $e, $bindings);
        }

        $this->dispatchEvent(new QueryExecuted(
            $this, $query, $bindings, elapsed_time($start)
        ));

        return $statement;
    }

    /**
     * @param callable $callable
     * @return mixed
     * @throws Throwable
     */
    public function transaction(callable $callable)
    {
        $this->pdo->beginTransaction();

        $this->dispatchEvent(new TransactionBeginning($this));

        try {
            $result = $callable($this);

            $this->pdo->commit();

            $this->dispatchEvent(new TransactionCommitted($this));

            return $result;
        } catch (Throwable $e) {
            $this->pdo->rollBack();

            $this->dispatchEvent(new TransactionRolledBack($this));

            throw $e;
        }
    }

    /**
     * @return Builder
     */
    public function select()
    {
        return $this->query()->select(...func_get_args());
    }

    /**
     * @param $table
     * @param null $as
     * @return Builder
     */
    public function table($table, $as = null)
    {
        return $this->query($table, $as);
    }

    /**
     * @param null $table
     * @param null $as
     * @return Builder
     */
    public function query($table = null, $as = null)
    {
        $query = new Builder($this);

        if (empty($table)) {
            return $query;
        }

        return $query->from($table, $as);
    }

    /**
     * @param $value
     * @return Expression
     */
    public function raw($value)
    {
        return new Expression($value);
    }

    /**
     * @return mixed
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function dispatchEvent(EventInterface $event)
    {
        if ($this->eventDispatcher) {
            $this->eventDispatcher->dispatch($event);
        }
    }

    /**
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}