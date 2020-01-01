<?php

namespace Mild\Support;

use Countable;
use Exception;
use ArrayAccess;
use ArrayIterator;
use JsonSerializable;
use IteratorAggregate;
use Mild\Contract\RepositoryInterface;

class Dot implements RepositoryInterface, Countable, ArrayAccess, JsonSerializable, IteratorAggregate
{
    /**
     * @var array
     */
    protected $items = [];
    /**
     * @var array
     */
    private $resolvedItems = [];

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }
        if (isset($this->resolvedItems[$key])) {
            return $this->resolvedItems[$key];
        }
        $items = $this->items;
        foreach ($this->parseKey($key) as $segment) {
            if (!isset($items[$segment])) {
                return $default;
            }
            $items = $items[$segment];
        }
        return $this->resolvedItems[$key] = $items;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        if (isset($this->items[$key]) || isset($this->resolvedItems[$key])) {
            return true;
        }
        $items = $this->items;
        $segments = $this->parseKey($key);
        $lastSegment = array_pop($segments);
        foreach ($segments as $segment) {
            if (!isset($items[$segment])) {
                return false;
            }

            $items = $items[$segment];
        }

        if (isset($items[$lastSegment])) {
            $this->resolvedItems[$key] = $items[$lastSegment];
            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $items =& $this->items;
        foreach ($this->parseKey($key) as $segment) {
            if (!isset($items[$segment])) {
                $items[$segment] = [];
            }

            $items[$segment] = Arr::wrap($items[$segment]);

            $items =& $items[$segment];
        }
        $items = $value;

        // Kita hanya menambahkan resolved items jika key nya contains titik [.].
        if (strpos($key, '.') !== false) {
            $this->resolvedItems[$key] = $items;
        }
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function add($key, $value)
    {
        $items =& $this->items;

        $value = Arr::wrap($value);

        foreach ($this->parseKey($key) as $segment) {
            if (!isset($items[$segment])) {
                $items[$segment] = [];
            }

            $items[$segment] = Arr::wrap($items[$segment]);

            $items =& $items[$segment];
        }
        $items = array_merge($items, $value);

        // Kita hanya menambahkan resolved items jika key nya contains titik [.].
        if (strpos($key, '.') !== false) {
            $this->resolvedItems[$key] = $items;
        }
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        $items =& $this->items;
        unset($this->resolvedItems[$key]);
        $segments = $this->parseKey($key);
        $key = array_pop($segments);
        foreach ($segments as $segment) {
            if (!isset($items[$segment])) {
                continue;
            }

            $items[$segment] = Arr::wrap($items[$segment]);

            $items =& $items[$segment];
        }
        unset($items[$key]);
    }

    /**
     * Alias metode dari getItems().
     *
     * @return array
     */
    public function all()
    {
        return $this->getItems();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return void
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param array $items
     * @return void
     */
    public function mergeItems(array $items)
    {
        $this->items += $items;
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->count();
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->items === [];
    }

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return $this->isEmpty() === false;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->put($offset);
    }

    /**
     * @param int $options
     * @param int $depth
     * @return string
     */
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toString()
    {
        $string = $this->toJson();
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg(), json_last_error());
        }

        return $string;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset($name)
    {
        $this->put($name);
    }

    /**
     * @param $key
     * @return array
     */
    protected function parseKey($key)
    {
        return explode('.', $key);
    }
}