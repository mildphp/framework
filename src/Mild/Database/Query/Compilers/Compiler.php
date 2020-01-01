<?php

namespace Mild\Database\Query\Compilers;

use Mild\Support\Arr;
use Mild\Database\Query\Builder;
use Mild\Database\Query\Expression;
use Mild\Database\Exceptions\CompilerException;
use Mild\Contract\Database\Query\BuilderInterface;
use Mild\Contract\Database\Query\CompilerInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

abstract class Compiler implements CompilerInterface
{
    /**
     * @var array
     */
    protected $operators = [];
    /**
     * @var array
     */
    protected $components = [];
    /**
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * @return array
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    /**
     * @param $operator
     * @return bool
     */
    public function isValidOperator($operator)
    {
        return in_array(strtolower($operator), $this->operators);
    }

    /**
     * @param $prefix
     * @return void
     */
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
    }

    /**
     * @param $value
     * @return string
     */
    public function wrap($value)
    {
        if ($this->isExpression($value)) {
            /**
             * @var Expression $value
             */
            return $value->getValue();
        }

        if ($this->isAlias($value)) {
            $segments = $this->splitAlias($value);
            return $this->wrap(Arr::first($segments)).' as '.$this->wrap(Arr::last($segments));
        }

        if (strpos($value, '.') !== false) {
            $segments = explode('.', $value);
            foreach ($segments as $key => $value) {
                $segments[$key] = $this->wrap($value);
            }
            return implode('.', $segments);
        }

        if ($value === '*') {
            return $value;
        }

        return $this->resolveWrap($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isAlias($value)
    {
        return stripos($value, ' as ') !== false;
    }

    /**
     * @param $value
     * @return array
     */
    public function splitAlias($value)
    {
        return preg_split('/\s+as\s+/i', $value);
    }

    /**
     * @param $value
     * @return bool
     */
    public function isExpression($value)
    {
        return $value instanceof Expression;
    }

    /**
     * @param $value
     * @return bool
     */
    public function isNotExpression($value)
    {
        return $this->isExpression($value) === false;
    }

    /**
     * @param array $bindings
     * @return array
     */
    abstract public function resolveBindingsForDelete(array $bindings);

    /**
     * @param array $bindings
     * @param array $values
     * @return array
     */
    abstract public function resolveBindingsForUpdate(array $bindings, array $values);

    /**
     * @param BuilderInterface $builder
     * @return array
     */
    protected function compileComponents($builder)
    {
        $components = [];

        foreach ($this->components as $key => $value) {
            if (isset($builder->{$key}) && $this->shouldCompile($builder, $key)) {
                $components[$key] = $this->{$value}($builder);
            }
        }

        return $components;
    }

    /**
     * @param $builder
     * @param $property
     * @return bool
     */
    protected function shouldCompile($builder, $property)
    {
        if (is_array($value = $builder->{$property})) {
            return !empty($value);
        }

        if (is_bool($value)) {
            return $value === true;
        }

        return true;
    }

    /**
     * @param $table
     * @return string
     */
    protected function wrapTable($table)
    {
        if ($this->isExpression($table)) {
            /**
             * @var Expression $table
             */
            return $table->getValue();
        }

        return $this->wrap($this->tablePrefix.$table);
    }

    /**
     * @param $columns
     * @return array
     */
    protected function wrapColumns($columns)
    {
        // Jika anda mengaliaskan column dalam array seperti: ['column' => 'alias']
        foreach (($columns = array_unique(Arr::wrap($columns))) as $key => $value) {
            if (is_string($key)) {
                $value = $key.' as '.$value;
            }

            $columns[$key] = $value;
        }

        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * @param $value
     * @return string
     */
    protected function resolveValueBinding($value)
    {
        if ($this->isExpression($value)) {
            /**
             * @var Expression $value
             */
            return $value->getValue();
        }

        return '?';
    }

    /**
     * @param string $message
     * @return CompilerException
     */
    protected function createCompilerException($message = '')
    {
        return new CompilerException($this, $message);
    }

    /**
     * @param $builder
     * @return string
     */
    protected function resolveComponent($builder)
    {
        if ($builder instanceof Builder) {
            return implode(' ', $this->compileComponents($builder));
        }

        return implode(' ', Arr::wrap($builder));
    }

    /**
     * @param $component
     * @return bool
     */
    protected function filterComponent($component)
    {
        return !empty($component);
    }

    /**
     * @param Builder $builder
     * @param string $tableAlias
     * @return string
     * @throws CompilerExceptionInterface
     */
    protected function compileSelectWithUnionAggregate($builder, $tableAlias = 'temp_table')
    {
        $query = $builder->connection->query()
            ->select($builder->columns);

        $query->aggregate = $builder->aggregate;
        $builder->aggregate = null;

        return $query->fromRaw('('.$builder->toSql().') as '.$this->wrap($tableAlias))
            ->toSql();
    }

    /**
     * @param $value
     * @return string
     */
    abstract protected function resolveWrap($value);
}