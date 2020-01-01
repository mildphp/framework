<?php

namespace Mild\Database\Connectors;

use PDO;
use Mild\Support\Arr;
use Mild\Database\Query\Compilers\SqliteCompiler;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;

class SqliteConnector extends Connector
{

    /**
     * @param array $config
     * @return PDO
     * @throws ConnectionExceptionInterface
     */
    public function connect(array $config)
    {
        return $this->createConnection('sqlite:'.Arr::get($config, 'database'), null, null, Arr::get($config, 'options', []));
    }

    /**
     * @return SqliteCompiler
     */
    public function getCompiler()
    {
        return new SqliteCompiler;
    }
}