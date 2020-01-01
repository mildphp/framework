<?php

namespace Mild\Cache;

use Mild\Contract\Cache\HandlerInterface;
use Mild\Contract\Cache\ManagerInterface;

class Manager implements ManagerInterface
{
    /**
     * @var string|null
     */
    protected $prefix;
    /**
     * @var HandlerInterface
     */
    protected $handler;

    /**
     * Manager constructor.
     *
     * @param HandlerInterface $handler
     * @param null $prefix
     */
    public function __construct(HandlerInterface $handler, $prefix = null)
    {
        $this->handler = $handler;
        $this->setPrefix($prefix);
    }

    /**
     * @return HandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->handler->get($this->resolveKey($key));
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     * @return void
     */
    public function set($key, $value, $expiration)
    {
        $this->handler->set($this->resolveKey($key), $value, $expiration);
    }

    /**
     * @param $key
     * @param callable $callback
     * @param $expiration
     * @return mixed
     */
    public function remember($key, callable $callback, $expiration)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $this->set($key, $value = call_user_func($callback), $expiration);

        return $value;
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        $this->handler->put($this->resolveKey($key));
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->handler->increment($this->resolveKey($key), $value);
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->handler->decrement($this->resolveKey($key), $value);
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->handler->flush();
    }

    /**
     * @param $key
     * @return string
     */
    protected function resolveKey($key)
    {
        if (empty($this->prefix)) {
            return $key;
        }

        return $this->prefix.'_'.$key;
    }
}