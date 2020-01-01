<?php

namespace Mild\Contract\Http;

use Psr\Http\Message\RequestInterface;

interface ClientHandlerInterface
{
    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param $key
     * @return bool
     */
    public function hasOption($key);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getOption($key, $default = null);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setOption($key, $value);

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request);
}