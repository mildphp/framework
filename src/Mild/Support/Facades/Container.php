<?php

namespace Mild\Support\Facades;

/**
 * Class Container
 *
 * @package Mild\Support\Facades
 * @see \Mild\Container\Container
 * @method static mixed get($key)
 * @method static array all()
 * @method static bool has($key)
 * @method static void put($key)
 * @method static void set($key, $value)
 * @method static void bind($key, $value)
 * @method static void alias($key, $value)
 * @method static mixed make($abstract, $arguments)
 * @method static bool offsetExists($offset)
 * @method static mixed offsetGet($offset)
 * @method static void offsetSet($offset, $value)
 * @method static void offsetUnset($offset)
 */
class Container extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'container';
    }
}