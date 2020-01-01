<?php

namespace Mild\Support\Facades;

use ArrayIterator;
use Mild\Contract\View\RepositoryInterface;

/**
 * Class ViewRepository
 *
 * @package \Mild\Support\Facades
 * @see \Mild\View\Repository
 * @method static bool has($key)
 * @method static mixed get($key, $default = null)
 * @method static void set($key, $value)
 * @method static void space($key, $path)
 * @method static void put($key)
 * @method static string getPath()
 * @method static array getSpaces()
 * @method static RepositoryInterface getSpace($key)
 * @method static bool hasSpace($key)
 * @method static void setSpace($key, RepositoryInterface $value)
 * @method static void setSpaces($spaces)
 * @method static void add($key, $value)
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
class ViewRepository extends Facade
{
    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'view.repository';
    }
}