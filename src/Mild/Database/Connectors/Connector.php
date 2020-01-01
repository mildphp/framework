<?php

namespace Mild\Database\Connectors;

use PDO;
use PDOException;
use Mild\Support\Str;
use Mild\Contract\Database\ConnectorInterface;
use Mild\Database\Exceptions\ConnectionException;

abstract class Connector implements ConnectorInterface
{
    /**
     * @var array
     */
    protected $defaultOptions = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES  => false
    ];

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->defaultOptions;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setDefaultOptions(array $options)
    {
        $this->defaultOptions = $options;
    }

    /**
     * @param $dsn
     * @param null $username
     * @param null $password
     * @param array $options
     * @return PDO
     */
    protected function createConnection($dsn, $username = null, $password = null, array $options = [])
    {
        $options = array_diff_key($this->defaultOptions, $options) + $options;

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            if ($this->shouldReconnect($e)) {
                return $this->createConnection($dsn, $username, $password, $options);
            }
            throw new ConnectionException($e);
        }
    }

    /**
     * @param PDOException $e
     * @return bool
     */
    protected function shouldReconnect(PDOException $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'Transaction() on null',
            'child connection forced to terminate due to client_idle_limit',
            'query_wait_timeout',
            'reset by peer',
            'Physical connection is not usable',
            'TCP Provider: Error code 0x68',
            'ORA-03114',
            'Packets out of order. Expected',
            'Adaptive Server connection failed',
            'Communication link failure',
        ]);
    }
}