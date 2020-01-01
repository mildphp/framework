<?php

namespace Mild\Contract\Database;

use PDO;
use Mild\Contract\Database\Query\CompilerInterface;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;

interface ConnectorInterface
{
    /**
     * @param array $config
     * @return PDO
     * @throws ConnectionExceptionInterface
     */
    public function connect(array $config);

    /**
     * @return mixed
     */
    public function getDefaultOptions();

    /**
     * @return CompilerInterface
     */
    public function getCompiler();

    /**
     * @param array $options
     * @return void
     */
    public function setDefaultOptions(array $options);
}