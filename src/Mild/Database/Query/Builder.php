<?php

namespace Mild\Database\Query;

use PDO;
use Closure;
use PDOStatement;
use Mild\Support\Arr;
use Mild\Support\Collection;
use InvalidArgumentException;
use Mild\Database\Connection;
use Mild\Database\Query\Compilers\Compiler;
use Mild\Contract\Database\Query\BuilderInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class Builder implements BuilderInterface
{
    /**
     * @var string
     */
    public $from;
    /**
     * @var mixed
     */
    public $lock;
    /**
     * @var int|null
     */
    public $limit;
    /**
     * @var int|null
     */
    public $offset;
    /**
     * @var string|null
     */
    public $aggregate;
    /**
     * @var Connection
     */
    public $connection;
    /**
     * @var array
     */
    public $joins = [];
    /**
     * @var array
     */
    public $orders = [];
    /**
     * @var array
     */
    public $unions = [];
    /**
     * @var array
     */
    public $groups = [];
    /**
     * @var array
     */
    public $wheres = [];
    /**
     * @var array
     */
    public $havings = [];
    /**
     * @var array
     */
    public $columns = [];
    /**
     * @var array
     */
    public $bindings = [
        'select'        => [],
        'from'          => [],
        'join'          => [],
        'where'         => [],
        'having'        => [],
        'union'         => [],
        'order'         => []
    ];
    /**
     * @var bool
     */
    public $distinct = false;

    /**
     * Builder constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function toSql()
    {
        if (empty($this->columns)) {
            $this->columns[] = '*';
        }

        $this->columns = array_unique($this->columns);

        if (count($this->columns) > 1) {
            $this->columns = array_filter($this->columns, [$this, 'filterColumns']);
        }

        return $this->connection->getCompiler()->compileSelect($this);
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return Arr::flatten($this->bindings);
    }

    /**
     * @return $this
     */
    public function select()
    {
        $this->columns = array_merge($this->columns, Arr::flatten(func_get_args()));

        return $this;
    }

    /**
     * @param $query
     * @param $as
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function selectSub($query, $as)
    {
        $bindings = [];

        if ($query instanceof Closure) {
            $query($query = $this->connection->query());
        }

        if ($query instanceof self) {
            $bindings = $query->getBindings();
            $query = $query->toSql();
        }

        return $this->selectRaw('('.$query.') as '.$this->connection->getCompiler()->wrap($as), $bindings);
    }

    /**
     * @param $raw
     * @param array $bindings
     * @return $this
     */
    public function selectRaw($raw, $bindings = [])
    {
        $this->select($this->connection->raw($raw));

        $this->addBinding('select', $bindings);

        return $this;
    }

    /**
     * @return Builder
     */
    public function distinct()
    {
        if (!empty($args = func_get_args())) {
            $this->distinct = is_array($args[0]) || is_bool($args[0]) ? $args[0] : $args;
        } else {
            $this->distinct = true;
        }

        return $this;
    }

    /**
     * @param $table
     * @param null $as
     * @return $this
     */
    public function from($table, $as = null)
    {
        $this->from = $table;

        if (!empty($as)) {
            $this->from .= ' as '.$as;
        }

        return $this;
    }

    /**
     * @param $raw
     * @param array $bindings
     * @return Builder
     */
    public function fromRaw($raw, $bindings = [])
    {
        $this->addBinding('from', $bindings);

        return $this->from($this->connection->raw($raw));
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @param bool $where
     * @return $this
     * @throws CompilerExceptionInterface
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = $this->connection->query($table);

        if ($table instanceof Closure) {
            /**
             * @var Compiler $compiler
             */
            $compiler = $this->connection->getCompiler();
            $table($query = $this->connection->query());
            if ($compiler->isAlias($table = $query->from)) {
                $table = Arr::last($compiler->splitAlias($table));
            }
            $join->fromRaw('('.$query->toSql().') '.$compiler->wrap($table))
                ->addBinding('join', $query->getBindings());
        }

        if ($first instanceof Closure) {
            $first($join);
        } else {
            $join->{($where ? 'where' : 'whereColumn')}($first, $operator, $second);
        }

        $this->joins[] = [
            'type'      => $type,
            'builder'   => $join
        ];

        $this->addBinding('join', $join->getBindings());

        return $this;
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param bool $where
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function leftJoin($table, $first, $operator = null, $second = null, $where = false)
    {
        return $this->join($table, $first, $operator, $second, 'left', $where);
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param bool $where
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function rightJoin($table, $first, $operator = null, $second = null, $where = false)
    {
        return $this->join($table, $first, $operator, $second, 'right', $where);
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $type
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function joinWhere($table, $first, $operator = null, $second = null, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function leftJoinWhere($table, $first, $operator = null, $second = null)
    {
        return $this->leftJoin($table, $first, $operator, $second, true);
    }

    /**
     * @param $table
     * @param $first
     * @param null $operator
     * @param null $second
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function rightJoinWhere($table, $first, $operator = null, $second = null)
    {
        return $this->rightJoin($table, $first, $operator, $second, true);
    }

    /**
     * @param $columns
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     * @throws CompilerExceptionInterface
     */
    public function where($columns, $operator = null, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->resolveValueOperatorFromArgs(func_num_args(), $value, $operator);

        foreach (Arr::wrap($columns) as $column) {
            if ($column instanceof Closure) {
                $this->whereNested($column, $boolean);
                continue;
            }

            if ($value === null) {
                $this->{($operator === '=' ? 'whereNull' : 'whereNotNull')}($column, $boolean);
                continue;
            }

            $bindings = $value;

            if ($value instanceof Closure) {
                $value($query = $this->connection->query());
                $value = $this->connection->raw('('.$query->toSql().')');
                $bindings = $query->getBindings();
            }

            $this->wheres[] = [
                'value'     => $value,
                'column'    => $column,
                'boolean'   => $boolean,
                'operator'  => $operator,
                'component' => 'compileBasicWhere'
            ];

            $this->addBinding('where', $bindings);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param null $operator
     * @param null $value
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function orWhere($columns, $operator = null, $value = null)
    {
        return $this->where($columns, $operator, $value, 'or');
    }

    /**
     * @param Closure $closure
     * @param string $boolean
     * @return $this
     */
    public function whereNested(Closure $closure, $boolean = 'and')
    {
        $closure($query = $this->connection->query($this->from));

        $this->wheres[] = [
            'builder'   => $query,
            'boolean'   => $boolean,
            'component' => 'compileWhereNestedWhere'
        ];

        $this->addBinding('where', $query->getBindings());

        return $this;
    }

    /**
     * @param $closure
     * @return Builder
     */
    public function orWhereNested($closure)
    {
        return $this->whereNested($closure, 'or');
    }

    /**
     * @param $columns
     * @param string $boolean
     * @return $this
     */
    public function whereNull($columns, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = [
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereNullWhere'
            ];
        }

        return $this;
    }

    /**
     * @param $columns
     * @return Builder
     */
    public function orWhereNull($columns)
    {
        return $this->whereNull($columns, 'or');
    }

    /**
     * @param $columns
     * @param string $boolean
     * @return $this
     */
    public function whereNotNull($columns, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = [
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereNotNullWhere'
            ];
        }

        return $this;
    }

    /**
     * @param $columns
     * @return Builder
     */
    public function orWhereNotNull($columns)
    {
        return $this->whereNotNull($columns, 'or');
    }

    /**
     * @param $columns
     * @param $values
     * @param string $boolean
     * @return $this
     * @throws CompilerExceptionInterface
     */
    public function whereIn($columns, $values, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {

            $bindings = $values;

            if ($values instanceof Closure) {
                $values($query = $this->connection->query());
                $values = $this->connection->raw($query->toSql());
                $bindings = $query->getBindings();
            }

            $this->wheres[] = [
                'values'    => $values,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereInWhere'
            ];

            $this->addBinding('where', $bindings);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $values
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function orWhereIn($columns, $values)
    {
        return $this->whereIn($columns, $values, 'or');
    }

    /**
     * @param $columns
     * @param $values
     * @param string $boolean
     * @return $this
     * @throws CompilerExceptionInterface
     */
    public function whereNotIn($columns, $values, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {

            $bindings = $values;

            if ($values instanceof Closure) {
                $values($query = $this->connection->query());
                $values = $this->connection->raw($query->toSql());
                $bindings = $query->getBindings();
            }

            $this->wheres[] = [
                'values'    => $values,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereNotInWhere'
            ];

            $this->addBinding('where', $bindings);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $values
     * @return Builder
     * @throws CompilerExceptionInterface
     */
    public function orWhereNotIn($columns, $values)
    {
        return $this->whereNotIn($columns, $values, 'or');
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @param string $boolean
     * @return $this
     */
    public function whereBetween($columns, $min, $max, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = [
                'min'       => $min,
                'max'       => $max,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereBetweenWhere'
            ];

            $this->addBinding('where', [$min, $max]);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @return Builder
     */
    public function orWhereBetween($columns, $min, $max)
    {
        return $this->whereBetween($columns, $min, $max, 'or');
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @param string $boolean
     * @return $this
     */
    public function whereNotBetween($columns, $min, $max, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = [
                'min'       => $min,
                'max'       => $max,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileWhereNotBetweenWhere'
            ];

            $this->addBinding('where', [$min, $max]);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @return Builder
     */
    public function orWhereNotBetween($columns, $min, $max)
    {
        return $this->whereNotBetween($columns, $min, $max, 'or');
    }

    /**
     * @param $first
     * @param null $operator
     * @param null $second
     * @param string $boolean
     * @return $this
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        [$second, $operator] = $this->resolveValueOperatorFromArgs(func_num_args(), $second, $operator);

        foreach (Arr::wrap($first) as $column) {
            $this->wheres[] = [
                'first'     => $column,
                'operator'  => $operator,
                'second'    => $second,
                'boolean'   => $boolean,
                'component' => 'compileWhereColumnWhere'
            ];
        }

        return $this;
    }

    /**
     * @param $first
     * @param null $operator
     * @param null $second
     * @return Builder
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * @param $raws
     * @param array $bindings
     * @param string $boolean
     * @return $this
     */
    public function whereRaw($raws, $bindings = [], $boolean = 'and')
    {
        foreach (Arr::wrap($raws) as $raw) {
            $this->wheres[] = [
                'raw'       => $raw,
                'boolean'   => $boolean,
                'component' => 'compileRawWhere'
            ];

            $this->addBinding('where', $bindings);
        }

        return $this;
    }

    /**
     * @param $raws
     * @param array $bindings
     * @return Builder
     */
    public function orWhereRaw($raws, $bindings = [])
    {
        return $this->whereRaw($raws, $bindings, 'or');
    }

    /**
     * @param $columns
     * @param string $direction
     * @return $this
     */
    public function orderBy($columns, $direction = 'asc')
    {
        if (!in_array($direction = strtolower($direction), ['asc', 'desc'])) {
            throw new InvalidArgumentException(sprintf(
                'Direction %s is invalid.', $direction
            ));
        }

        $this->orders[] = [
            'column'    => Arr::wrap($columns),
            'direction' => $direction
        ];

        return $this;
    }

    /**
     * @param $raw
     * @param array $bindings
     * @return $this
     */
    public function orderByRaw($raw, $bindings = [])
    {
        $this->orders[] = [
            'raw' => $raw
        ];

        $this->addBinding('order', Arr::wrap($bindings));

        return $this;
    }

    /**
     * @return $this
     */
    public function groupBy()
    {
        foreach (($args = func_get_args()) as $group) {
            $this->groups = array_merge(
                $this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * @param $columns
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function having($columns, $operator = null, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->resolveValueOperatorFromArgs(func_num_args(), $value, $operator);

        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = [
                'value'     => $value,
                'column'    => $column,
                'boolean'   => $boolean,
                'operator'  => $operator,
                'component' => 'compileBasicHaving'
            ];

            $this->addBinding('having', $value);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param null $operator
     * @param null $value
     * @return Builder
     */
    public function orHaving($columns, $operator = null, $value = null)
    {
        return $this->having($columns, $operator, $value, 'or');
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @param string $boolean
     * @return $this
     */
    public function havingBetween($columns, $min, $max, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = [
                'min'       => $min,
                'max'       => $max,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileHavingBetweenHaving'
            ];

            $this->addBinding('having', [$min, $max]);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @return Builder
     */
    public function orHavingBetween($columns, $min, $max)
    {
        return $this->havingBetween($columns, $min, $max, 'or');
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @param string $boolean
     * @return $this
     */
    public function havingNotBetween($columns, $min, $max, $boolean = 'and')
    {
        foreach (Arr::wrap($columns) as $column) {
            $this->havings[] = [
                'min'       => $min,
                'max'       => $max,
                'column'    => $column,
                'boolean'   => $boolean,
                'component' => 'compileHavingNotBetweenHaving'
            ];

            $this->addBinding('having', [$min, $max]);
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $min
     * @param $max
     * @return Builder
     */
    public function orHavingNotBetween($columns, $min, $max)
    {
        return $this->havingNotBetween($columns, $min, $max, 'or');
    }

    /**
     * @param $raws
     * @param array $bindings
     * @param string $boolean
     * @return $this
     */
    public function havingRaw($raws, $bindings = [], $boolean = 'and')
    {
        foreach (Arr::wrap($raws) as $raw) {
            $this->havings[] = [
                'raw'       => $raw,
                'boolean'   => $boolean,
                'component' => 'compileRawHaving'
            ];

            $this->addBinding('having', $bindings);
        }

        return $this;
    }

    /**
     * @param $raws
     * @param array $bindings
     * @return Builder
     */
    public function orHavingRaw($raws, $bindings = [])
    {
        return $this->havingRaw($raws, $bindings, 'or');
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $query
     * @param bool $all
     * @return $this
     */
    public function union($query, $all = false)
    {
        if ($query instanceof Closure) {
            $query($query = $this->connection->query());
        }

        $this->unions[] = [
            'all'       => $all,
            'builder'   => $query
        ];

        $this->addBinding('union', $query->getBindings());

        return $this;
    }

    /**
     * @param $query
     * @return Builder
     */
    public function unionAll($query)
    {
        return $this->union($query, true);
    }

    /**
     * @return int
     * @throws CompilerExceptionInterface
     */
    public function count()
    {
        return (int) $this->aggregate(__FUNCTION__, func_get_args());
    }

    /**
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function min()
    {
        return $this->aggregate(__FUNCTION__, func_get_args());
    }

    /**
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function max()
    {
        return $this->aggregate(__FUNCTION__, func_get_args());
    }

    /**
     * @return int
     * @throws CompilerExceptionInterface
     */
    public function sum()
    {
        return (int) $this->aggregate(__FUNCTION__, func_get_args());
    }

    /**
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function avg()
    {
        return $this->aggregate(__FUNCTION__, func_get_args());
    }

    /**
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function average()
    {
        return $this->avg(func_get_args());
    }

    /**
     * @param $function
     * @param array $columns
     * @return mixed|null
     * @throws CompilerExceptionInterface
     */
    public function aggregate($function, $columns = ['*'])
    {
        $this->aggregate = $function;

        $results = $this->get($columns);

        if (!$results->isEmpty()) {
            return $results[0]->{$function};
        }

        return null;
    }

    /**
     * @return bool
     * @throws CompilerExceptionInterface
     */
    public function exists()
    {
        $results = $this->runSelect(
            $this->connection->execute($this->connection->getCompiler()->compileExists($this), $this->getBindings())
        );

        return (bool) $results[0]->exists;
    }

    /**
     * @return Collection
     * @throws CompilerExceptionInterface
     */
    public function get()
    {
        return new Collection($this->runSelect(
            $this->connection->execute($this->select(...func_get_args())->toSql(), $this->getBindings())
        ));
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

            if (($total = $results->count()) === 0 || $total !== $count) {
                break;
            }
        }

        return true;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function lock($value = true)
    {
        $this->lock = $value;

        return $this;
    }

    /**
     * @return Builder
     */
    public function lockForUpdate()
    {
        return $this->lock(true);
    }

    /**
     * @return Builder
     */
    public function sharedLock()
    {
        return $this->lock(false);
    }

    /**
     * @return mixed
     * @throws CompilerExceptionInterface
     */
    public function first()
    {
        return $this->limit(1)->get()->first();
    }

    /**
     * @return bool
     */
    public function delete()
    {
        /**
         * @var Compiler $compiler
         */
        $compiler = $this->connection->getCompiler();

        return (bool) $this->connection->execute($compiler->compileDelete($this), $this->filterBindings($compiler->resolveBindingsForDelete($this->bindings)))->rowCount();
    }

    /**
     * @param $values
     * @return bool
     */
    public function insert($values)
    {
        return (bool) $this->connection->execute($this->connection->getCompiler()->compileInsert($this, $values), $this->filterBindings(array_values($values)))->rowCount();
    }

    /**
     * @param $values
     * @param null $name
     * @return int
     */
    public function insertGetId($values, $name = null)
    {
        $this->insert($values);

        return (int) $this->connection->getPdo()->lastInsertId($name);
    }

    /**
     * @param $values
     * @return bool
     */
    public function insertOrIgnore($values)
    {
        return (bool) $this->connection->execute($this->connection->getCompiler()->compileInsertOrIgnore($this, $values), $this->filterBindings(array_values($values)))->rowCount();
    }

    /**
     * @param $values
     * @param null $name
     * @return int
     */
    public function insertOrIgnoreGetId($values, $name = null)
    {
        $this->insertOrIgnore($values);

        return (int) $this->connection->getPdo()->lastInsertId($name);
    }

    /**
     * @param $values
     * @return bool
     */
    public function update($values)
    {
        /**
         * @var Compiler $compiler
         */
        $compiler = $this->connection->getCompiler();

        return (bool) $this->connection->execute($compiler->compileUpdate($this, $values), $this->filterBindings($compiler->resolveBindingsForUpdate($this->bindings, $values)));
    }

    /**
     * @param $columns
     * @param int $value
     * @param array $extra
     * @return bool
     */
    public function increment($columns, int $value = 1, array $extra = [])
    {
        $compiler = $this->connection->getCompiler();

        foreach (Arr::wrap($columns) as $column) {
            $extra[$column] = $this->connection->raw($compiler->wrap($column).' + '.$value);
        }

        return $this->update($extra);
    }

    /**
     * @param $columns
     * @param int $value
     * @param array $extra
     * @return bool
     */
    public function decrement($columns, int $value = 1, array $extra = [])
    {
        $compiler = $this->connection->getCompiler();

        foreach (Arr::wrap($columns) as $column) {
            $extra[$column] = $this->connection->raw($compiler->wrap($column).' - '.$value);
        }

        return $this->update($extra);
    }

    /**
     * @param $column
     * @return bool
     */
    protected function filterColumns($column)
    {
        if (!empty($this->columns) && $column === '*') {
            return false;
        }

        return true;
    }

    /**
     * @param $count
     * @param $value
     * @param $operator
     * @return array
     */
    protected function resolveValueOperatorFromArgs($count, $value, $operator)
    {
        if ($count === 2 || !$this->connection->getCompiler()->isValidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        return [$value, $operator];
    }

    /**
     * @param $type
     * @param $value
     * @return void
     */
    protected function addBinding($type, $value)
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $this->filterBindings($value)));
    }

    /**
     * @param $bindings
     * @return array
     */
    protected function filterBindings($bindings)
    {
        return array_values(array_filter(Arr::wrap($bindings), [$this->connection->getCompiler(), 'isNotExpression']));
    }

    /**
     * @param PDOStatement $statement
     * @return mixed
     */
    protected function runSelect($statement)
    {
        return $statement->fetchAll(PDO::FETCH_OBJ);
    }
}