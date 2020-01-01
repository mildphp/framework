<?php

namespace Mild\Support\Facades;

use PDO;
use PDOStatement;
use Mild\Database\Query\Builder;
use Mild\Database\Query\Expression;
use Mild\Contract\Event\EventInterface;
use Mild\Contract\View\CompilerInterface;
use Mild\Contract\Event\EventDispatcherInterface;

/**
 * Class Database
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Database\Connection
 * @method static PDOStatement execute($query, $bindings)
 * @method static mixed transaction($callable)
 * @method static Builder select()
 * @method static Builder table($table, $as = null)
 * @method static Builder query($table = null, $as = null)
 * @method static Expression raw($value)
 * @method static mixed getDriverName()
 * @method static PDO getPdo()
 * @method static CompilerInterface getCompiler()
 * @method static void dispatchEvent(EventInterface $event)
 * @method static EventDispatcherInterface|null getEventDispatcher()
 * @method static void setEventDispatcher(EventDispatcherInterface $eventDispatcher)
 */
class Database extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'database';
    }
}