<?php

namespace Mild\Contract\Http;

use Psr\Http\Client\RequestExceptionInterface as PsrRequestExceptionInterface;

interface RequestExceptionInterface extends PsrRequestExceptionInterface
{
    /**
     * @return ResponseInterface
     */
    public function getResponse();
}