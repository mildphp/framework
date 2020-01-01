<?php

namespace Mild\Contract\Http;

use Throwable;

interface HttpExceptionInterface extends Throwable
{
    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @return string
     */
    public function getReasonPhrase();
}