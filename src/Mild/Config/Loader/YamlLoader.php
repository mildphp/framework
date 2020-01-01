<?php

namespace Mild\Config\Loader;

use Symfony\Component\Yaml\Parser;
use Mild\Contract\Config\LoaderInterface;

class YamlLoader implements LoaderInterface
{
    /**
     * @param $file
     * @return array|mixed
     */
    public function load($file)
    {
        return (new Parser)->parseFile($file);
    }
}