<?php

namespace Mild\Contract\Container;

use ArrayAccess;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends ArrayAccess, PsrContainerInterface
{
    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function bind($key, $value);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function alias($key, $value);

    /**
     * @param $key
     * @return void
     */
    public function put($key);

    /**
     * @return array
     */
    public function all();

    /**
     * @param $abstract
     * @param array $arguments
     * @return mixed
     */
    public function make($abstract, array $arguments = []);
}