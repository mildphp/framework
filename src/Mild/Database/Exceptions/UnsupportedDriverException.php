<?php

namespace Mild\Database\Exceptions;

use Exception;
use Mild\Contract\Database\Exceptions\UnsupportedDriverExceptionInterface;

class UnsupportedDriverException extends Exception implements UnsupportedDriverExceptionInterface
{
    //
}