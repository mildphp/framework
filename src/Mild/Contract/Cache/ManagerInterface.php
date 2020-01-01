<?php

namespace Mild\Contract\Cache;

interface ManagerInterface extends HandlerInterface
{
    /**
     * @return HandlerInterface
     */
    public function getHandler();

    /**
     * @return string|null
     */
    public function getPrefix();

    /**
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     * @return void
     */
    public function set($key, $value, $expiration);

    /**
     * @param $key
     * @param callable $callback
     * @param $expiration
     * @return mixed
     */
    public function remember($key, callable $callback, $expiration);

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
    public function increment($key, $value = 1);

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrement($key, $value = 1);
}