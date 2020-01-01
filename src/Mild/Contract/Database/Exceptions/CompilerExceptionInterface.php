<?php

namespace Mild\Contract\Database\Exceptions;

use Throwable;
use Mild\Contract\Database\Query\CompilerInterface;

interface CompilerExceptionInterface extends Throwable
{
    /**
     * @return CompilerInterface
     */
    public function getCompiler();
}