<?php

namespace Mild\Contract\Container;

use Throwable;

interface InvalidAbstractExceptionInterface extends Throwable
{
    /**
     * @return mixed
     */
    public function getAbstract();
}