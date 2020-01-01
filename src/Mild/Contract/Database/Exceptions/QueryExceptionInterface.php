<?php

namespace Mild\Contract\Database\Exceptions;

interface QueryExceptionInterface extends ConnectionExceptionInterface
{
    /**
     * @return string
     */
    public function getQuery();

    /**
     * @return array
     */
    public function getBindings();
}