<?php

namespace Mild\Config\Loader;

use Mild\Contract\Config\LoaderInterface;

class XmlLoader implements LoaderInterface
{

    /**
     * @param $file
     * @return array
     * @throws LoaderException
     */
    public function load($file)
    {
        libxml_use_internal_errors(true);

        $data = json_decode(json_encode(simplexml_load_file($file, null, LIBXML_NOERROR)), true);

        if (($error = libxml_get_last_error()) !== false) {
            libxml_clear_errors();
            throw new LoaderException($error->message, $error->file, $error->line);
        }

        return $data;
    }
}