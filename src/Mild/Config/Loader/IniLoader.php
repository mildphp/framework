<?php

namespace Mild\Config\Loader;

use Throwable;
use Mild\Contract\Config\LoaderInterface;

class IniLoader implements LoaderInterface
{

    /**
     * @param $file
     * @return array
     * @throws LoaderException
     */
    public function load($file)
    {
        try {
            return parse_ini_file($file);
        } catch (Throwable $e) {
            throw new LoaderException($e->getMessage(), $file);
        }
    }
}