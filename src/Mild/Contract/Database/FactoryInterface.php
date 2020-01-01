<?php

namespace Mild\Contract\Database;

interface FactoryInterface
{
    /**
     * @param array $config
     * @return ConnectionInterface
     */
    public function make(array $config);
}