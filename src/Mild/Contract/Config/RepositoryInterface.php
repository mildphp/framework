<?php

namespace Mild\Contract\Config;

use Mild\Contract\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param $paths
     * @param string $prefix
     * @return void
     */
    public function load($paths, $prefix = '');
}