<?php

namespace Mild\Config\Loader;

use Mild\Contract\Config\LoaderInterface;

class JsonLoader implements LoaderInterface
{

    /**
     * @param $file
     * @return array|mixed
     * @throws LoaderException
     */
    public function load($file)
    {
        $items = json_decode(file_get_contents($file), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LoaderException(json_last_error_msg(), $file, 0);
        }

        return $items;
    }
}