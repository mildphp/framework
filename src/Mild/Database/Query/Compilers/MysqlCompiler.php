<?php

namespace Mild\Database\Query\Compilers;

use Mild\Support\Arr;
use Mild\Database\Query\Builder;
use Mild\Contract\Database\Query\BuilderInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class MysqlCompiler extends Compiler
{
    /**
     * @var array
     */
    protected $components = [
        'columns'   => 'compileColumns',
        'from'      => 'compileFrom',
        'joins'     => 'compileJoins',
        'wheres'    => 'compileWheres',
        'groups'    => 'compileGroups',
        'havings'   => 'compileHavings',
        'orders'    => 'compileOrders',
        'limit'     => 'compileLimit',
        'offset'    => 'compileOffset'
    ];

    /**
     * @var array
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*'
    ];

    /**
     * @param BuilderInterface|Builder $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function compileExists(BuilderInterface $builder)
    {
        return 'select exists('.$builder->toSql().') as '.$this->wrapColumns('exists');
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function compileSelect(BuilderInterface $builder)
    {
        if (!empty($builder->unions) && null !== $builder->aggregate) {
            return $this->compileSelectWithUnionAggregate($builder);
        }

        $sql = $this->resolveComponent($builder);

        if (!empty($builder->unions)) {
            $sql = '('.$sql.') '.$this->compileUnions($builder);
        }

        if (null !== $builder->lock) {
            $sql .= ' '.$this->compileLock($builder);
        }

        return $sql;
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @param array $values
     * @return string
     */
    public function compileInsert(BuilderInterface $builder, array $values)
    {
        $command = 'insert';

        $sql = $command.' into '.$this->wrapTable($builder->from);

        if (empty($values)) {
            $sql .= ' default values';
        } else {
            $sql .= ' ('.implode(', ', array_map([$this, 'wrapColumns'], array_keys($values))).') values ('.implode(', ', array_map([$this, 'resolveValueBinding'], $values)).')';
        }

        return $sql;
    }

    /**
     * @param BuilderInterface $builder
     * @param array $values
     * @return string
     */
    public function compileInsertOrIgnore(BuilderInterface $builder, array $values)
    {
        return 'insert ignore'.substr($this->compileInsert($builder, $values), 6);
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @return string|void
     */
    public function compileDelete(BuilderInterface $builder)
    {
        $command = 'delete';
        $table = $this->wrapTable($builder->from);

        if (!empty($builder->joins)) {
            $command .= ' '.Arr::last($this->splitAlias($table));
        }

        if (!empty($followingSql = implode(' ', array_filter([$this->compileJoins($builder), $this->compileWheres($builder)], [$this, 'filterComponent'])))) {
            $followingSql = ' '.$followingSql;
        }

        return $command.' from '.$table.$followingSql;
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @param array $values
     * @return string|void
     */
    public function compileUpdate(BuilderInterface $builder, array $values)
    {
        $i = 0;

        $parameters = [];

        foreach ($values as $key => $value) {
            $parameters[] = ($i === 0 ? 'set ' : '').$this->wrap($key).' = '.$this->resolveValueBinding($value);
            ++$i;
        }

        if (!empty($followingSql = implode(' ', array_filter([$this->compileJoins($builder), implode(', ', $parameters), $this->compileWheres($builder)], [$this, 'filterComponent'])))) {
            $followingSql = ' '.$followingSql;
        }

        return 'update '.$this->wrapTable($builder->from).$followingSql;
    }

    /**
     * @param array $bindings
     * @return array
     */
    public function resolveBindingsForDelete(array $bindings)
    {
        return Arr::flatten(Arr::put($bindings, 'select'));
    }

    /**
     * @param array $bindings
     * @param array $values
     * @return array
     */
    public function resolveBindingsForUpdate(array $bindings, array $values)
    {
        return array_merge(Arr::wrap($bindings['join']), array_values($values), Arr::flatten(Arr::put($bindings, ['select', 'join'])));
    }

    /**
     * @param $value
     * @return string
     */
    protected function resolveWrap($value)
    {
        return '`'.$value.'`';
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileColumns($builder)
    {
        if ($builder->aggregate) {
            return $this->compileAggregate($builder);
        }

        $select = 'select ';

        if ($builder->distinct) {
            $select .= 'distinct ';
        }

        return $select.$this->wrapColumns($builder->columns);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileAggregate($builder)
    {
        $column = $this->wrapColumns($builder->columns);

        if ($builder->distinct) {
            $column = is_array($builder->distinct) ? $this->wrapColumns($builder->distinct) : $column;
            if ($column !== '*') {
                $column = 'distinct '.$column;
            }
        }

        return 'select '.$builder->aggregate.'('.$column.') as '.$this->wrap($builder->aggregate);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileFrom($builder)
    {
        return 'from '.$this->wrapTable($builder->from);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileJoins($builder)
    {
        $clauses = [];

        foreach ($builder->joins as $join) {
            /**
             * @var Builder $joinBuilder
             */
            $joinBuilder = $join['builder'];

            $table = $this->wrapTable($joinBuilder->from);

            $clauses[] = $join['type'].' join '.(empty($joinBuilder->joins) ? $table : '('.$table.' '.$this->compileJoins($joinBuilder).')').' '.$this->compileWheres($joinBuilder, true);
        }

        return implode(' ', $clauses);
    }

    /**
     * @param $builder
     * @param bool $join
     * @return string
     */
    protected function compileWheres($builder, $join = false)
    {
        $i = 0;

        $clauses = [];

        foreach ($builder->wheres as $where) {
            $clauses[] = ($i !== 0 ? $where['boolean'] : (($join === true) ? 'on' : 'where')).' '.$this->{$where['component']}($where);
            ++$i;
        }

        return implode(' ', $clauses);
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileBasicWhere($where)
    {
        return $this->wrap($where['column']).' '.$where['operator'].' '.$this->resolveValueBinding($where['value']);
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereNestedWhere($where)
    {
        $i = 0;

        $clauses = [];

        foreach ($where['builder']->wheres as $where) {
            $clauses[] = ($i !== 0 ? $where['boolean'] : '').' '.$this->{$where['component']}($where);
            ++$i;
        }

        return '('.implode(' ', $clauses).')';
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereNullWhere($where)
    {
        return $this->wrap($where['column']).' is null';
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereNotNullWhere($where)
    {
        return $this->wrap($where['column']).' is not null';
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereInWhere($where)
    {
        return $this->wrap($where['column']).' in ('.implode(', ', array_map([$this, 'resolveValueBinding'], Arr::wrap($where['values']))).')';
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereNotInWhere($where)
    {
        return $this->wrap($where['column']).' not in ('.implode(', ', array_map([$this, 'resolveValueBinding'], Arr::wrap($where['values']))).')';
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereBetweenWhere($where)
    {
        return $this->wrap($where['column']).' between '.$this->resolveValueBinding($where['min']).' and '.$this->resolveValueBinding($where['max']);
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereNotBetweenWhere($where)
    {
        return $this->wrap($where['column']).' not between '.$this->resolveValueBinding($where['min']).' and '.$this->resolveValueBinding($where['max']);
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileWhereColumnWhere($where)
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }

    /**
     * @param $where
     * @return string
     */
    protected function compileRawWhere($where)
    {
        return $where['raw'];
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileGroups($builder)
    {
        return 'group by '.implode(', ', array_map([$this, 'wrap'], $builder->groups));
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileHavings($builder)
    {
        $i = 0;

        $clauses = [];

        foreach ($builder->havings as $having) {
            $clauses[] = ($i === 0 ? 'having' : $having['boolean']).' '.$this->{$having['component']}($having, $builder);
            ++$i;
        }

        return implode(' ', $clauses);
    }

    /**
     * @param $having
     * @return string
     */
    protected function compileBasicHaving($having)
    {
        return $this->wrap($having['column']).' '.$having['operator'].' '.$this->resolveValueBinding($having['value']);
    }

    /**
     * @param $having
     * @return string
     */
    protected function compileHavingBetweenHaving($having)
    {
        return $this->wrap($having['column']).' between '.$this->resolveValueBinding($having['min']). ' and '.$this->resolveValueBinding($having['max']);
    }

    /**
     * @param $having
     * @return string
     */
    protected function compileHavingNotBetweenHaving($having)
    {
        return $this->wrap($having['column']).' not between '.$this->resolveValueBinding($having['min']). ' and '.$this->resolveValueBinding($having['max']);
    }

    /**
     * @param $having
     * @return string
     */
    protected function compileRawHaving($having)
    {
        return $having['raw'];
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileOrders($builder)
    {
        $clauses = [];

        foreach ($builder->orders as $order) {
            $clauses[] = $order['raw'] ?? $this->wrapColumns($order['column']).' '.$order['direction'];
        }

        return 'order by '.implode(', ', $clauses);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileLimit($builder)
    {
        return 'limit '.$builder->limit;
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileOffset($builder)
    {
        return 'offset '.$builder->offset;
    }

    /**
     * @param $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    protected function compileUnions($builder)
    {
        $clauses = [];

        foreach ($this->flattenUnions($builder->unions) as $union) {

            /**
             * @var Builder $unionBuilder
             */
            $unionBuilder = $union['builder'];

            $clauses[] = ($union['all'] ? 'union all' : 'union').' ('.$unionBuilder->toSql().')';
        }

        return implode(' ', $clauses);
    }

    /**
     * @param array $unions
     * @return array
     */
    protected function flattenUnions($unions)
    {
        $results = [];

        foreach ($unions as $union) {
            $results[] = $union;
            if (!empty($union['builder']->unions)) {
                $results = array_merge($results, $this->flattenUnions($union['builder']->unions));
                $union['builder']->unions = [];
            }
        }

        return $results;
    }

    /**
     * @param $builder
     * @return string
     */
    protected function compileLock($builder)
    {
        if (false === is_bool($builder->lock)) {
            return $builder->lock;
        }

        return $builder->lock ? 'for update' : 'lock in share mode';
    }
}