<?php

namespace Mild\Support\Facades;

use ArrayIterator;
use SessionHandlerInterface;

/**
 * Class Session
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Session\Manager
 * @method static string getId()
 * @method static string getName()
 * @method static string getPrefix()
 * @method static SessionHandlerInterface getHandler()
 * @method static bool isStarted()
 * @method static void setId($id)
 * @method static void setPrefix($prefix)
 * @method static void start()
 * @method static void regenerate(bool $destroy = false)
 * @method static void save()
 * @method static mixed get($key, $default = null)
 * @method static bool has($key)
 * @method static void set($key, $value)
 * @method static void add($key, $value)
 * @method static void put($key)
 * @method static array all()
 * @method static array getItems()
 * @method static void setItems($items)
 * @method static void mergeItems($items)
 * @method static int total()
 * @method static int count()
 * @method static bool isEmpty()
 * @method static bool isNotEmpty()
 * @method static bool offsetExists($offset)
 * @method static mixed offsetGet($offset)
 * @method static void offsetSet($offset, $value)
 * @method static void offsetUnset($offset)
 * @method static string toJson(int $options = 0, int $depth = 512)
 * @method static array jsonSerialize()
 * @method static ArrayIterator getIterator()
 * @method static string toString()
 */
class Session extends Facade
{

    /**
     * @return string
     */
    protected static function getAccessor()
    {
        return 'session';
    }
}