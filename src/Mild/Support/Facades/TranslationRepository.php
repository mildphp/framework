<?php

namespace Mild\Support\Facades;

use ArrayIterator;
use Mild\Contract\Translation\RepositoryInterface;

/**
 * Class TranslationRepository
 *
 * @package \Mild\Support\Facades\Mild\Support\Facades
 * @see \Mild\Translation\Repository
 * @method static RepositoryInterface getSpace($key)
 * @method static bool hasSpace($key)
 * @method static void setSpace($key, RepositoryInterface $value)
 * @method static array getSpaces()
 * @method static void setSpaces($spaces)
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

class TranslationRepository extends Facade
{
    /**
     * @return object|string
     */
    protected static function getAccessor()
    {
        return 'translation.repository';
    }
}