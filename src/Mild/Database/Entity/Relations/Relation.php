<?php

namespace Mild\Database\Entity\Relations;

use Closure;
use Mild\Support\Collection;
use Mild\Database\Entity\Model;
use Mild\Database\Entity\Builder;

/**
 * Class Relation
 *
 * @package Mild\Database\Entity\Relations
 * @method $this with(string|array $relation)
 * @method $this without(string|array $relation)
 * @method $this select(...$columns)
 * @method $this where(string $column, string|mixed $operator = '=', string|null $value = null, string $boolean = 'and')
 * @method $this orWhere(string $column, string|mixed $operator = '=', string|null $value = null)
 * @method $this whereNested(Closure $closure, $boolean = 'and')
 * @method $this orWhereNested($closure)
 * @method $this whereNull($columns, $boolean = 'and')
 * @method $this orWhereNull($columns)
 * @method $this whereNotNull($columns, $boolean = 'and')
 * @method $this orWhereNotNull($columns)
 * @method $this whereIn(string $column, array $values, string $boolean = 'and')
 * @method $this orWhereIn(string $column , array $values)
 * @method $this whereNotIn($columns, $values, $boolean = 'and')
 * @method $this orWhereNotIn($columns, $values)
 * @method $this whereBetween($columns, $min, $max, $boolean = 'and')
 * @method $this orWhereBetween($columns, $min, $max)
 * @method $this whereNotBetween($columns, $min, $max, $boolean = 'and')
 * @method $this orWhereNotBetween($columns, $min, $max)
 * @method $this whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method $this orWhereColumn($first, $operator = null, $second = null)
 * @method $this whereRaw($raws, $bindings = [], $boolean = 'and')
 * @method $this orWhereRaw($raws, $bindings = [])
 * @method $this orderBy($columns, $direction = 'asc')
 * @method $this orderByRaw($raw, $bindings = [])
 * @method $this groupBy(...$columns)
 * @method $this having($columns, $operator = null, $value = null, $boolean = 'and')
 * @method $this orHaving($columns, $operator = null, $value = null)
 * @method $this havingBetween($columns, $min, $max, $boolean = 'and')
 * @method $this orHavingBetween($columns, $min, $max)
 * @method $this havingNotBetween($columns, $min, $max, $boolean = 'and')
 * @method $this orHavingNotBetween($columns, $min, $max)
 * @method $this havingRaw($raws, $bindings = [], $boolean = 'and')
 * @method $this orHavingRaw($raws, $bindings = [])
 * @method $this union($query, $all = false)
 * @method $this unionAll($query)
 * @method $this limit(int $limit)
 * @method $this offset(int $offset)
 * @method $this selectSub($query, $as)
 * @method $this selectRaw($raw, $bindings = [])
 * @method $this distinct()
 * @method $this lock($value = true)
 * @method $this lockForUpdate()
 * @method $this sharedLock()
 */
abstract class Relation
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var Model
     */
    public $model;
    /**
     * @var Model
     */
    public $related;
    /**
     * @var Builder
     */
    public $builder;
    /**
     * @var string
     */
    public $primaryKey;
    /**
     * @var string
     */
    public $foreignKey;

    /**
     * Relation constructor.
     * @param Model $model
     * @param Model $related
     * @param $primaryKey
     * @param $foreignKey
     */
    public function __construct(Model $model, Model $related, $primaryKey, $foreignKey)
    {
        $this->model = $model;
        $this->related = $related;
        $this->primaryKey = $primaryKey;
        $this->foreignKey = $foreignKey;
        $this->builder = $related->query();
        $this->name = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, array $arguments = [])
    {
        if (($result = $this->builder->$name(...$arguments)) instanceof Builder === false) {
            return $result;
        }

        return $this;
    }

    /**
     * @param Collection|null $collection
     * @return mixed
     */
    abstract public function execute(Collection $collection = null);
}