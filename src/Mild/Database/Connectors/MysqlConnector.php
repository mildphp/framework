<?php

namespace Mild\Database\Connectors;

use PDO;
use Mild\Support\Arr;
use Mild\Database\Query\Compilers\MysqlCompiler;
use Mild\Database\Exceptions\ConnectionException;

class MysqlConnector extends Connector
{
    /**
     * @param array $config
     * @return PDO
     * @throws ConnectionException
     */
    public function connect(array $config)
    {
        $pdo = $this->createConnection(
            $this->createDsnFromConfig($config),
            Arr::get($config,'username'),
            Arr::get($config,'password'),
            Arr::get($config, 'options', [])
        );

        $this->configureModes($pdo, $config);
        $this->configureEncoding($pdo, $config);
        $this->configureTimezone($pdo, $config);

        return $pdo;
    }

    /**
     * @return MysqlCompiler
     */
    public function getCompiler()
    {
        return new MysqlCompiler;
    }

    /**
     * @param $config
     * @return string
     */
    protected function createDsnFromConfig($config)
    {
        $host = Arr::get($config,'host');
        $database = Arr::get($config,'database');

        if (Arr::has($config,'unix_socket')) {
            return sprintf('mysql:unix_socket=%s;dbname=%s', $config['unix_socket'], $database);
        }

        if (Arr::has($config,'port')) {
            return sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $config['port'], $database);
        }

        return sprintf('mysql:host=%s;dbname=%s', $host, $database);
    }

    /**
     * @param PDO $pdo
     * @param $config
     */
    protected function configureModes($pdo, $config)
    {
        if (Arr::has($config,'modes')) {
            $pdo->prepare(sprintf(
                'set session sql_mode=\'%s\'', implode(',', Arr::wrap($config['modes']))
            ));
        } elseif (Arr::has($config,'strict')) {
            if (version_compare($pdo->getAttribute(PDO::ATTR_SERVER_VERSION), '8.0.11') >= 0) {
                $pdo->prepare(
                    'set session sql_mode=\'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION\''
                )->execute();
            } else {
                $pdo->prepare(
                    'set session sql_mode=\'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION\''
                )->execute();
            }
        } else {
            $pdo->prepare('set session sql_mode=\'NO_ENGINE_SUBSTITUTION\'')->execute();
        }
    }

    /**
     * @param PDO $pdo
     * @param $config
     * @return void
     */
    protected function configureEncoding($pdo, $config)
    {
        if (Arr::has($config,'charset')) {
            if (Arr::has($config,'collation')) {
                $pdo->prepare(sprintf(
                    'set names \'%s\' collate \'%s\'', $config['charset'], $config['collation']
                ))->execute();
            } else {
                $pdo->prepare(sprintf(
                    'set names \'%s\'', $config['charset']
                ))->execute();
            }
        }
    }

    /**
     * @param PDO $pdo
     * @param $config
     * @return void
     */
    protected function configureTimezone($pdo, $config)
    {
        if (Arr::has($config,'timezone')) {
            $pdo->prepare(sprintf(
                'set time_zone=\'%s\'', $config['timezone']
            ))->execute();
        }
    }
}