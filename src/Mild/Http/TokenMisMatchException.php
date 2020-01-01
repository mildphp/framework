<?php

namespace Mild\Http;

class TokenMisMatchException extends HttpException
{
    /**
     * TokenMisMatchException constructor.
     */
    public function __construct()
    {
        parent::__construct(419, 'Authentication Timeout');
    }
}