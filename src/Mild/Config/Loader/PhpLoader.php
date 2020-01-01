<?php

namespace Mild\Config\Loader;

use Throwable;
use Mild\Contract\Config\LoaderInterface;

class PhpLoader implements LoaderInterface
{

    /**
     * @param $file
     * @return array
     * @throws LoaderException
     */
    public function load($file)
    {
        try {
            return require ''.$file.'';
        } catch (Throwable $e) {
            throw new LoaderException($e->getMessage(), $e->getFile(), $e->getLine());
        }
    }
}