<?php

namespace Mild\Database\Exceptions;

use PDOException;
use Mild\Contract\Database\Exceptions\ConnectionExceptionInterface;

class ConnectionException extends PDOException implements ConnectionExceptionInterface
{
    /**
     * @var PDOException
     */
    protected $pdoException;

    /**
     * ConnectionException constructor.
     *
     * @param PDOException $pdoException
     */
    public function __construct(PDOException $pdoException)
    {
        $this->pdoException = $pdoException;
        $this->code = $pdoException->getCode();
        parent::__construct($pdoException->getMessage());
    }

    /**
     * @return PDOException
     */
    public function getPdoException()
    {
        return $this->pdoException;
    }
}