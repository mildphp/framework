<?php

namespace Mild\Database\Connectors;

use PDO;
use Mild\Support\Arr;
use Mild\Database\Query\Compilers\SqlSrvCompiler;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;

class SqlSrvConnector extends Connector
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false
    ];

    /**
     * @param array $config
     * @return PDO
     * @throws ConnectionExceptionInterface
     */
    public function connect(array $config)
    {
        return $this->createConnection($this->getDsn($config), Arr::get($config, 'username'), Arr::get($config, 'password'), Arr::get($config, 'options', []));
    }

    /**
     * @return SqlSrvCompiler
     */
    public function getCompiler()
    {
        return new SqlSrvCompiler;
    }

    /**
     * @param array $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        if ($this->prefersOdbc($config)) {
            return $this->getOdbcDsn($config);
        }

        if (in_array('sqlsrv', $this->getAvailableDrivers())) {
            return $this->getSqlSrvDsn($config);
        } else {
            return $this->getDblibDsn($config);
        }
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function prefersOdbc($config)
    {
        return in_array('odbc', $this->getAvailableDrivers()) &&
            ($config['odbc'] ?? null) === true;
    }

    /**
     * @param $config
     * @return string
     */
    protected function getDblibDsn($config)
    {
        return $this->buildConnectString('dblib', array_merge([
            'host' => $this->buildHostString($config, ':'),
            'dbname' => $config['database'],
        ], Arr::only($config, ['appname', 'charset', 'version'])));
    }

    /**
     * @param $config
     * @return string
     */
    protected function getOdbcDsn($config)
    {
        return isset($config['odbc_datasource_name'])
            ? 'odbc:'.$config['odbc_datasource_name'] : '';
    }

    /**
     * @param $config
     * @return string
     */
    protected function getSqlSrvDsn($config)
    {
        $arguments = [
            'Server' => $this->buildHostString($config, ','),
        ];

        if (isset($config['database'])) {
            $arguments['Database'] = $config['database'];
        }

        if (isset($config['readonly'])) {
            $arguments['ApplicationIntent'] = 'ReadOnly';
        }

        if (isset($config['pooling']) && $config['pooling'] === false) {
            $arguments['ConnectionPooling'] = '0';
        }

        if (isset($config['appname'])) {
            $arguments['APP'] = $config['appname'];
        }

        if (isset($config['encrypt'])) {
            $arguments['Encrypt'] = $config['encrypt'];
        }

        if (isset($config['trust_server_certificate'])) {
            $arguments['TrustServerCertificate'] = $config['trust_server_certificate'];
        }

        if (isset($config['multiple_active_result_sets']) && $config['multiple_active_result_sets'] === false) {
            $arguments['MultipleActiveResultSets'] = 'false';
        }

        if (isset($config['transaction_isolation'])) {
            $arguments['TransactionIsolation'] = $config['transaction_isolation'];
        }

        if (isset($config['multi_subnet_failover'])) {
            $arguments['MultiSubnetFailover'] = $config['multi_subnet_failover'];
        }

        return $this->buildConnectString('sqlsrv', $arguments);
    }

    /**
     * @param $driver
     * @param array $arguments
     * @return string
     */
    protected function buildConnectString($driver, array $arguments)
    {
        return $driver.':'.implode(';', array_map(function ($key) use ($arguments) {
                return sprintf('%s=%s', $key, $arguments[$key]);
            }, array_keys($arguments)));
    }

    /**
     * @param array $config
     * @param $separator
     * @return mixed|string
     */
    protected function buildHostString(array $config, $separator)
    {
        if (empty($config['port'])) {
            return $config['host'];
        }

        return $config['host'].$separator.$config['port'];
    }

    /**
     * @return array
     */
    protected function getAvailableDrivers()
    {
        return PDO::getAvailableDrivers();
    }
}