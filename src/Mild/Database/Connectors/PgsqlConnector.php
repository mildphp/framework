<?php

namespace Mild\Database\Connectors;

use PDO;
use Mild\Support\Arr;
use Mild\Database\Query\Compilers\PgsqlCompiler;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;

class PgsqlConnector extends Connector
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
        $pdo = $this->createConnection(
            $this->createDsnFromConfig($config),
            Arr::get($config, 'username'),
            Arr::get($config, 'password'),
            Arr::get($config, 'options', [])
        );

        $this->configureEncoding($pdo, $config);
        $this->configureTimezone($pdo, $config);
        $this->configureSchema($pdo, $config);
        $this->configureApplicationName($pdo, $config);

        return $pdo;
    }

    /**
     * @return PgsqlCompiler
     */
    public function getCompiler()
    {
        return new PgsqlCompiler;
    }

    /**
     * @param $config
     * @return string
     */
    protected function createDsnFromConfig($config)
    {
        $host = Arr::get($config, 'host');
        $database = Arr::get($config, 'database');

        if (!Arr::has($config, 'port')) {
            $dsn = sprintf('pgsql:host=%s;dbname=%s', $host, $database);
        } else {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $config['port'], $database);
        }

        foreach (['sslmode', 'sslcert', 'sslkey', 'sslrootcert'] as $option) {
            if (Arr::has($config, $option)) {
                $dsn .= ';'.$option.'='.$config[$option];
            }
        }

        return $dsn;
    }

    /**
     * @param PDO $pdo
     * @param $config
     */
    protected function configureEncoding($pdo, $config)
    {
        if (Arr::has($config, 'charset')) {
            $pdo->prepare(sprintf(
                'set names \'%s\'', $config['charset']
            ))->execute();
        }
    }

    /**
     * @param PDO $pdo
     * @param $config
     */
    protected function configureTimezone($pdo, $config)
    {
        if (Arr::has($config, 'timezone')) {
            $pdo->prepare(sprintf(
                'set time zone \'%s\'', $config['timezone']
            ))->execute();
        }
    }

    /**
     * @param PDO $pdo
     * @param $config
     */
    protected function configureSchema($pdo, $config)
    {
        if (Arr::has($config, 'schema')) {
            $pdo->prepare(sprintf(
                'set search_path to %s', implode(', ', array_map([$this, 'formatSchema'], Arr::wrap($config['schema'])))
            ))->execute();
        }
    }

    /**
     * @param $schema
     * @return string
     */
    protected function formatSchema($schema)
    {
        return '\''.$schema.'\'';
    }

    /**
     * @param PDO $pdo
     * @param $config
     */
    protected function configureApplicationName($pdo, $config)
    {
        if (Arr::has($config, 'application_name')) {
            $pdo->prepare(sprintf(
                'set application_name to \'%s\'', $config['application_name']
            ))->execute();
        }
    }
}