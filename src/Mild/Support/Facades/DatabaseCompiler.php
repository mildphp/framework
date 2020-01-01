<?php

namespace Mild\Support\Facades;

use Mild\Contract\Database\Query\BuilderInterface;

/**
 * Class DatabaseCompiler
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Contract\Database\Query\CompilerInterface
 * @method static array getOperators()
 * @method static string wrap($value)
 * @method static string getTablePrefix()
 * @method static bool isValidOperator($operator)
 * @method static void setTablePrefix($prefix)
 * @method static string compileExists(BuilderInterface $builder)
 * @method static string compileSelect(BuilderInterface $builder)
 * @method static string compileDelete(BuilderInterface $builder)
 * @method static string compileInsert(BuilderInterface $builder, $values)
 * @method static string compileInsertOrIgnore(BuilderInterface $builder, $values)
 * @method static string compileUpdate(BuilderInterface $builder, $values)
 */
class DatabaseCompiler extends Facade
{
    /**
     * @return object|string
     */
    protected static function getAccessor()
    {
        return 'database.compiler';
    }
}