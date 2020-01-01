<?php

namespace Mild\Http;

class NotFoundHttpException extends HttpException
{
    /**
     * NotFoundHttpException constructor.
     */
    public function __construct()
    {
        parent::__construct(404);
    }
}