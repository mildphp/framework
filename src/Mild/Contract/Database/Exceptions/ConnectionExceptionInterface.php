<?php

namespace Mild\Contract\Database\Exceptions;

use Throwable;
use PDOException;

interface ConnectionExceptionInterface extends Throwable
{
    /**
     * @return PDOException
     */
    public function getPdoException();
}