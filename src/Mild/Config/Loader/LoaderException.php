<?php


namespace Mild\Config\Loader;

use ErrorException;
use Mild\Contract\Config\LoaderExceptionInterface;

class LoaderException extends ErrorException implements LoaderExceptionInterface
{
    /**
     * LoaderException constructor.
     *
     * @param string $message
     * @param string $filename
     * @param int $lineno
     */
    public function __construct($message, $filename = __FILE__, $lineno = __LINE__)
    {
        parent::__construct($message, 0, 1, $filename, $lineno);
    }
}