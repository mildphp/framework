<?php

namespace Mild\Http;

class PostTooLargeException extends HttpException
{
    /**
     * PostTooLargeException constructor.
     */
    public function __construct()
    {
        parent::__construct(413);
    }
}