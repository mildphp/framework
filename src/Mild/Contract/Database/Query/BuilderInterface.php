<?php

namespace Mild\Contract\Database\Query;

use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

interface BuilderInterface
{
    /**
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function toSql();
}