<?php

namespace Mild\Contract;

use Throwable;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

interface ErrorHandlerInterface
{
    /**
     * @return ApplicationInterface
     */
    public function getApplication();

    /**
     * @param Throwable $e
     * @return void
     */
    public function report(Throwable $e);

    /**
     * @param Throwable $e
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function renderResponse(Throwable $e, ServerRequestInterface $request);
}