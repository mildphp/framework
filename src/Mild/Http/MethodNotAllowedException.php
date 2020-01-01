<?php

namespace Mild\Http;

class MethodNotAllowedException extends HttpException
{
    /**
     * MethodNotAllowedException constructor.
     */
    public function __construct()
    {
        parent::__construct(405);
    }
}