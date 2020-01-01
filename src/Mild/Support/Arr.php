<?php

namespace Mild\Support;

class Arr
{
    /**
     * @param $value
     * @return array
     */
    public static function wrap($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === null) {
            return [];
        }
        return [$value];
    }

    /**
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        return self::createDotInstance($array)->get($key, $default);
    }

    /**
     * @param $array
     * @param $keys
     * @return array
     */
    public static function only($array, $keys)
    {
        return array_intersect_key($array, array_flip(self::wrap($keys)));
    }

    /**
     * @param $array
     * @param $keys
     * @return bool
     */
    public static function in($array, $keys)
    {
        foreach (self::wrap($keys) as $key) {
            if (in_array($key, $array)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $array
     * @param $keys
     * @return bool
     */
    public static function has($array, $keys)
    {
        $dot = self::createDotInstance($array);

        $return = false;

        foreach (self::wrap($keys) as $key) {
            $return = $dot->has($key);
        }

        return $return;
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function set($array, $key, $value)
    {
        $dot = self::createDotInstance($array);

        $dot->set($key, $value);

        return $dot->all();
    }

    /**
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function add($array, $key, $value)
    {
        $dot = self::createDotInstance($array);

        $dot->add($key, $value);

        return $dot->all();
    }

    /**
     * @param $array
     * @param $keys
     * @return array
     */
    public static function put($array, $keys)
    {
        $dot = self::createDotInstance($array);

        foreach (self::wrap($keys) as $key) {
            $dot->put($key);
        }

        return $dot->all();
    }

    /**
     * @param $array
     * @return string
     */
    public static function query($array)
    {
        return http_build_query($array, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param $array
     * @return mixed|null
     */
    public static function first($array)
    {
        foreach (self::wrap($array) as $value) {
            return $value;
        }

        return null;
    }

    /**
     * @param $array
     * @return mixed
     */
    public static function last($array)
    {
        $array = self::wrap($array);

        return end($array);
    }

    /**
     * @param $array
     * @param callable $callback
     * @return array
     */
    public static function where($array, callable $callback)
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param $array
     * @param $depth
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $results = [];

        foreach ($array as $item) {
            if (! is_array($item)) {
                $results[] = $item;
                continue;
            }

            $values = $depth === 1
                ? array_values($item)
                : self::flatten($item, $depth - 1);

            foreach ($values as $value) {
                $results[] = $value;
            }
        }

        return $results;
    }

    /**
     * @param array $items
     * @return Dot
     */
    private static function createDotInstance($items = [])
    {
        $dot = new Dot;

        $dot->setItems($items);

        return $dot;
    }
}