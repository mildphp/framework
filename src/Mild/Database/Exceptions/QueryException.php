<?php

namespace Mild\Database\Exceptions;

use PDOException;
use Mild\Support\Str;
use Mild\Contract\Database\Exceptions\QueryExceptionInterface;

class QueryException extends ConnectionException implements QueryExceptionInterface
{
    /**
     * @var string
     */
    protected $query;
    /**
     * @var array
     */
    protected $bindings;

    /**
     * QueryException constructor.
     *
     * @param $query
     * @param PDOException $previous
     * @param array $bindings
     */
    public function __construct($query, PDOException $previous, array $bindings = [])
    {
        $this->query = $query;
        $this->bindings = $bindings;
        $previous->message = sprintf('%s (SQL: %s)', $previous->getMessage(), Str::replace('?', $bindings, $query));

        parent::__construct($previous);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }
}