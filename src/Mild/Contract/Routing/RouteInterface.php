<?php

namespace Mild\Contract\Routing;

interface RouteInterface extends RouteAttributeInterface
{
    /**
     * @return array
     */
    public function getMethods();

    /**
     * @return string
     */
    public function getPattern();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getParameter($key, $default = null);

    /**
     * @param $host
     * @param $path
     * @return bool
     */
    public function match($host, $path);
}