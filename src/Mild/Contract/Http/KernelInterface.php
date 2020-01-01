<?php

namespace Mild\Contract\Http;

interface KernelInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request);
}