<?php

namespace Mild\Database;

use Mild\Support\Str;
use InvalidArgumentException;
use Mild\Support\Traits\Macroable;
use Mild\Database\Connectors\PgsqlConnector;
use Mild\Contract\Database\FactoryInterface;
use Mild\Database\Connectors\MysqlConnector;
use Mild\Database\Connectors\SqliteConnector;
use Mild\Database\Connectors\SqlSrvConnector;
use Mild\Contract\Database\ConnectorInterface;
use Mild\Contract\Database\ConnectionInterface;
use Mild\Database\Exceptions\UnsupportedDriverException;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;
use Mild\Contract\Database\Exceptions\UnsupportedDriverExceptionInterface;

class Factory implements FactoryInterface
{
    use Macroable;

    /**
     * @param array $config
     * @return ConnectionInterface
     * @throws ConnectionExceptionInterface
     * @throws UnsupportedDriverExceptionInterface
     */
    public function make(array $config)
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('Missing driver on configuration.');
        }

        $connector = $this->createConnector($config['driver']);

        $compiler = $connector->getCompiler();

        if (isset($config['prefix'])) {
            $compiler->setTablePrefix($config['prefix']);
        }

        return new Connection($connector->connect($config), $compiler);
    }

    /**
     * @param $driver
     * @return ConnectorInterface
     * @throws UnsupportedDriverExceptionInterface
     */
    public function createConnector($driver)
    {
        if (method_exists($this, $method = sprintf('Create%sConnector', Str::studly($driver)))) {
            return $this->$method();
        }

        if (self::hasMacro($driver)) {
            return $this->$driver();
        }

        throw new UnsupportedDriverException(sprintf(
            'Unsupported [%s] driver.', $driver
        ));
    }

    /**
     * @return MysqlConnector
     */
    public function createMysqlConnector()
    {
        return new MysqlConnector;
    }

    /**
     * @return PgsqlConnector
     */
    public function createPgsqlConnector()
    {
        return new PgsqlConnector;
    }

    /**
     * @return SqliteConnector
     */
    public function createSqliteConnector()
    {
        return new SqliteConnector;
    }

    /**
     * @return SqlSrvConnector
     */
    public function createSqlSrvConnector()
    {
        return new SqlSrvConnector;
    }
}