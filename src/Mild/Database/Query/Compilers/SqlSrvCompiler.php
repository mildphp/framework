<?php

namespace Mild\Database\Query\Compilers;

use Mild\Support\Arr;
use Mild\Database\Query\Builder;
use Mild\Database\Exceptions\CompilerException;
use Mild\Contract\Database\Query\BuilderInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class SqlSrvCompiler extends Compiler
{
    /**
     * @var array
     */
    protected $operators = [
        '=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
        'like', 'not like', 'ilike',
        '&', '&=', '|', '|=', '^', '^='
    ];
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
        'orders'    => 'compileOrders'
    ];

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
        return array_merge(array_values($values), Arr::flatten(Arr::put($bindings, 'select')));
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function compileExists(BuilderInterface $builder)
    {
        $builder->columns = [];

        return $builder->selectRaw('1 '.$this->wrap('exists'))->limit(1)->toSql();
    }

    /**
     * @param BuilderInterface $builder
     * @param array $values
     * @return string
     * @throws CompilerException
     */
    public function compileInsertOrIgnore(BuilderInterface $builder, array $values)
    {
        throw new CompilerException($this, 'Unsupported Insert Ignore');
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

        if (!$builder->offset) {
            $sql = $this->resolveComponent($builder);
            if (!empty($builder->unions)) {
                $sql = $builder->connection->query()->selectRaw('* from ('.$sql.') as '.$this->wrap('temp_table'))->toSql().' '.$this->compileUnions($builder);
            }

            return $sql;
        }

        $components = $this->compileComponents($builder);

        if (!isset($components['orders'])) {
            $components['orders'] = 'order by (select 0)';
        }

        $builder->selectRaw('row_number() over ('.$components['orders'].') as '.$this->wrap('row_num'));

        $components['columns'] = $this->compileColumns($builder);

        unset($components['orders']);

        $query = $builder->connection->query()
            ->fromRaw('('.$this->resolveComponent($components).') as '.$this->wrap('temp_table'))
            ->orderByRaw($this->wrap('row_num'));

        $start = $builder->offset + 1;

        if ($builder->limit > 0) {
            $query->whereBetween('row_num', $query->connection->raw($start), $query->connection->raw($builder->offset + $builder->limit));
        } else {
            $query->where('row_num', '<=', $query->connection->raw($start));
        }

        return $query->toSql();
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @return string
     */
    public function compileDelete(BuilderInterface $builder)
    {
        if ($this->isAlias($table = $this->wrapTable($builder->from))) {
            $table = Arr::last($this->splitAlias($table));
        }

        if (!empty($followingSql = $this->resolveComponentForUpdateOrDelete($builder))) {
            $followingSql = ' '.$followingSql;
        }

        return 'delete '.$table.$followingSql;
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

        $sql .= ' ('.implode(', ', array_map([$this, 'wrapColumns'], array_keys($values))).') values ('.implode(', ', array_map([$this, 'resolveValueBinding'], $values)).')';

        return $sql;
    }

    /**
     * @param BuilderInterface|Builder $builder
     * @param array $values
     * @return string
     */
    public function compileUpdate(BuilderInterface $builder, array $values)
    {
        $i = 0;

        $parameters = [];

        foreach ($values as $key => $value) {
            $parameters[] = ($i === 0 ? 'set ' : '').$this->wrap($key).' = '.$this->resolveValueBinding($value);
            ++$i;
        }

        if (!empty($followingSql = implode(' ', array_filter([implode(', ', $parameters), $this->resolveComponentForUpdateOrDelete($builder)], [$this, 'filterComponent'])))) {
            $followingSql = ' '.$followingSql;
        }

        if ($this->isAlias($table = $builder->from)) {
            $table = Arr::last($this->splitAlias($table));
        }

        return 'update '.$this->wrapTable($table).$followingSql;
    }

    /**
     * @param $value
     * @return string
     */
    protected function resolveWrap($value)
    {
        return '['.$value.']';
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

        if ($builder->limit > 0 && $builder->offset <= 0) {
            $select .= 'top '.$builder->limit.' ';
        }

        if ($builder->distinct) {
            $select .= 'distinct ';
        }

        return $select.$this->wrapColumns($builder->columns);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function compileFrom($builder)
    {
        return 'from '.$this->wrapTable($builder->from).((null !== $builder->lock) ? ' with(rowlock,'.($builder->lock ? ' updlock, ' : ' ').'holdlock)' : '');
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
     * @throws CompilerExceptionInterface
     */
    protected function compileUnions($builder)
    {
        $clauses = [];

        foreach ($builder->unions as $union) {

            /**
             * @var Builder $unionBuilder
             */
            $unionBuilder = $union['builder'];

            $clauses[] = ($union['all'] ? 'union all' : 'union').' '.$builder->connection->query()->selectRaw('* from ('.$unionBuilder->toSql().') as '.$this->wrap('temp_table'))->toSql();
        }

        return implode(' ', $clauses);
    }

    /**
     * @param Builder $builder
     * @return string
     */
    protected function resolveComponentForUpdateOrDelete($builder)
    {
        return implode(' ', array_filter([$this->compileFrom($builder), $this->compileJoins($builder), $this->compileWheres($builder)], [$this, 'filterComponent']));
    }
}