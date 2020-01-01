<?php

namespace Mild\Contract\Http;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

interface ResponseInterface extends PsrResponseInterface
{
    /**
     * @return void
     */
    public function send();
}