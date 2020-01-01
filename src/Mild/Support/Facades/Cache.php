<?php

namespace Mild\Support\Facades;

use Mild\Contract\Cache\HandlerInterface;

/**
 * Class Cache
 *
 * @package Mild\Support\Facades
 * @see \Mild\Cache\Manager
 * @method static HandlerInterface getHandler()
 * @method static string|null getPrefix()
 * @method static void setPrefix($prefix)
 * @method static mixed get($key)
 * @method static bool has($key)
 * @method static void set($key, $value, $expiration)
 * @method static mixed remember($key, $callback, $expiration)
 * @method static void put($key)
 * @method static int increment($key, int $value = 1)
 * @method static int decrement($key, int $value = 1)
 * @method static void flush()
 */
class Cache extends Facade
{
    /**
     * @return object|string
     */
    protected static function getAccessor()
    {
        return 'cache';
    }
}