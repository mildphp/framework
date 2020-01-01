<?php

namespace Mild\Cache;

use RuntimeException;
use Mild\Contract\Cache\HandlerInterface;

class ApcHandler implements HandlerInterface
{
    /**
     * @var bool
     */
    private $isApcu;

    /**
     * ApcHandler constructor.
     */
    public function __construct()
    {
        if (!extension_loaded('apc') || !extension_loaded('apcu')) {
            throw new RuntimeException('Extension apc or apcu is not loaded.');
        }

        $this->isApcu = function_exists('apcu_fetch');
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->isApcu ? apc_fetch($key) : apcu_fetch($key);
    }

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     * @return void
     */
    public function set($key, $value, $expiration)
    {
        $this->isApcu ? apc_store($key, $value, $expiration) : apcu_store($key, $value, $expiration);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        $this->isApcu ? apcu_delete($key) : apc_delete($key);
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function increment($key, int $value = 1)
    {
        return $this->isApcu ? apcu_inc($key, $value) : apc_inc($key, $value);
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrement($key, int $value = 1)
    {
        return $this->isApcu ? apcu_dec($key, $value) : apc_dec($key, $value);
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->isApcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}