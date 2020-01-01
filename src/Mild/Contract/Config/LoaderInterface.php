<?php

namespace Mild\Contract\Config;

interface LoaderInterface
{
    /**
     * @param $file
     * @return array
     * @throws LoaderExceptionInterface
     */
    public function load($file);
}