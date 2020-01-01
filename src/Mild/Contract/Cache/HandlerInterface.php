<?php

namespace Mild\Contract\Cache;

interface HandlerInterface
{
    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     * @return void
     */
    public function set($key, $value, $expiration);

    /**
     * @param $key
     * @return void
     */
    public function put($key);

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function increment($key, int $value = 1);

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrement($key, int $value = 1);

    /**
     * @return void
     */
    public function flush();
}