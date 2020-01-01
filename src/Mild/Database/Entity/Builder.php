<?php

namespace Mild\Database\Entity;

use Closure;
use Mild\Support\Arr;
use Mild\Support\Collection;
use Mild\Database\Connection;
use Mild\Database\Query\Builder as Query;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

/**
 * Class Builder
 *
 * @package Mild\Database\Entity
 * @method Builder select(...$columns)
 * @method Builder where(string $column, string|mixed $operator = '=', string|null $value = null, string $boolean = 'and')
 * @method Builder orWhere(string $column, string|mixed $operator = '=', string|null $value = null)
 * @method Builder whereNested(Closure $closure, $boolean = 'and')
 * @method Builder orWhereNested($closure)
 * @method Builder whereNull($columns, $boolean = 'and')
 * @method Builder orWhereNull($columns)
 * @method Builder whereNotNull($columns, $boolean = 'and')
 * @method Builder orWhereNotNull($columns)
 * @method Builder whereIn(string $column, array $values, string $boolean = 'and')
 * @method Builder orWhereIn(string $column , array $values)
 * @method Builder whereNotIn($columns, $values, $boolean = 'and')
 * @method Builder orWhereNotIn($columns, $values)
 * @method Builder whereBetween($columns, $min, $max, $boolean = 'and')
 * @method Builder orWhereBetween($columns, $min, $max)
 * @method Builder whereNotBetween($columns, $min, $max, $boolean = 'and')
 * @method Builder orWhereNotBetween($columns, $min, $max)
 * @method Builder whereColumn($first, $operator = null, $second = null, $boolean = 'and')
 * @method Builder orWhereColumn($first, $operator = null, $second = null)
 * @method Builder whereRaw($raws, $bindings = [], $boolean = 'and')
 * @method Builder orWhereRaw($raws, $bindings = [])
 * @method Builder orderBy($columns, $direction = 'asc')
 * @method Builder orderByRaw($raw, $bindings = [])
 * @method Builder groupBy(...$columns)
 * @method Builder having($columns, $operator = null, $value = null, $boolean = 'and')
 * @method Builder orHaving($columns, $operator = null, $value = null)
 * @method Builder havingBetween($columns, $min, $max, $boolean = 'and')
 * @method Builder orHavingBetween($columns, $min, $max)
 * @method Builder havingNotBetween($columns, $min, $max, $boolean = 'and')
 * @method Builder orHavingNotBetween($columns, $min, $max)
 * @method Builder havingRaw($raws, $bindings = [], $boolean = 'and')
 * @method Builder orHavingRaw($raws, $bindings = [])
 * @method Builder union($query, $all = false)
 * @method Builder unionAll($query)
 * @method Builder limit(int $limit)
 * @method Builder offset(int $offset)
 * @method int insertGetId(array $attributes)
 * @method bool update(array $attributes = [])
 * @method string toSql()
 * @method array getBindings()
 * @method Builder selectSub($query, $as)
 * @method Builder selectRaw($raw, $bindings = [])
 * @method Builder distinct()
 * @method int count(...$columns)
 * @method mixed|null min(...$columns)
 * @method mixed|null max(...$columns)
 * @method mixed|null sum(...$columns)
 * @method mixed|null avg(...$columns)
 * @method mixed|null average(...$columns)
 * @method mixed|null aggregate($function, $columns = ['*'])
 * @method bool exists()
 * @method Builder lock($value = true)
 * @method Builder lockForUpdate()
 * @method Builder sharedLock()
 * @method bool insert($values)
 * @method bool insertOrIgnore($values)
 * @method bool insertOrIgnoreGetId($values, $name = null)
 * @method bool increment($columns, int $value = 1, array $extra = [])
 * @method bool decrement($columns, int $value = 1, array $extra = [])
 */
class Builder
{
    /**
     * @var Model
     */
    public $model;
    /**
     * @var Query
     */
    public $query;
    /**
     * @var Connection
     */
    public $connection;
    /**
     * @var array
     */
    public $relations = [];

    /**
     * Builder constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->connection = $model->connection();
        $this->query = $this->connection->query($this->model->getTable());
    }

    /**
     * @return $this
     */
    public function with()
    {
        foreach (Arr::flatten(func_get_args()) as $relation) {
            if (strpos($relation, '.') === false) {
                $this->relations[$relation] = Model::getContainer()->make([$this->model, $relation]);
                continue;
            }

            [$key, $value] = explode('.', $relation, 2);

            if (!isset($this->relations[$key])) {
                $this->relations[$key] = Model::getContainer()->make([$this->model, $key]);
            }

            $this->relations[$key]->with($value);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function without()
    {
        foreach (Arr::flatten(func_get_args()) as $relation) {
            if (strpos($relation, '.') === false) {
                unset($this->relations[$relation]);
                continue;
            }

            [$key, $value] = explode('.', $relation, 2);

            if (isset($this->relations[$key])) {
                $this->relations[$key]->without($value);
            }
        }

        return $this;
    }

    /**
     * @param $id
     * @return Model
     * @throws CompilerExceptionInterface
     */
    public function find($id)
    {
        return $this->where($this->model->getKey(), '=', $id)->first();
    }

    /**
     * @return Collection
     * @throws CompilerExceptionInterface
     */
    public function get()
    {
        $this->select(func_get_args());

        if (!empty($this->query->columns) && !Arr::in($this->query->columns, ['*', ($key = $this->model->getKey())])) {
            $this->select($key);
        }

        $collection = $this->query->get()->map(function ($item) {
            return $this->model->newInstance((array) $item);
        });

        if (!empty($this->relations)) {
            foreach ($this->relations as $relation) {
                $relation->execute($collection);
            }
        }

        return $collection;
    }

    /**
     * @return Model
     * @throws CompilerExceptionInterface
     */
    public function first()
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * @param null $id
     * @return true
     */
    public function delete($id = null)
    {
        if (null !== $id) {
            $this->where($this->model->getKey(), '=', $id);
        }

        return $this->query->delete();
    }

    /**
     * @return bool
     */
    public function save()
    {
        $key = $this->model->getKey();
        $attributes = $this->model->getAttributes();

        if (null === ($id = $this->model->{$key})) {
            $this->create($attributes);
        } else {
            $this->where($key, '=', $id)->update(Arr::put($attributes, $key));
        }

        return true;
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes = [])
    {
        $attributes[$this->model->getKey()] = $this->insertGetId($attributes);

        return $this->model->newInstance($attributes);
    }

    /**
     * @param $count
     * @param callable $callback
     * @return bool
     * @throws CompilerExceptionInterface
     */
    public function chunk($count, callable $callback)
    {
        for ($i = 1; $i <= $count; ++$i) {
            if ($callback($results = $this->limit($count)->offset((($i - 1) * $count))->get(), $i) === false) {
                return false;
            }

            /**
             * @var Collection $results
             */
            if (($total = $results->count()) === 0 || $total !== $count) {
                break;
            }
        }

        return true;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        if (($result = $this->query->$name(...$arguments)) instanceof Query) {
            return $this;
        }

        return $result;
    }
}