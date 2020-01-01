<?php

namespace Mild\Support;

use ArrayAccess;
use ReflectionClass;
use ReflectionProperty;
use ReflectionException;

class Optional implements ArrayAccess
{
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var bool
     */
    private $isArray = false;
    /**
     * @var bool
     */
    private $isObject = false;

    /**
     * Optional constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;

        $this->isObject = is_object($value);

        $this->isArray = is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * @param $key
     * @return bool
     * @throws ReflectionException
     */
    public function has($key)
    {
        if ($this->isArray) {
            return isset($this->value[$key]);
        } elseif ($this->isObject) {
            return isset($this->value->{$key}) ?: (new ReflectionClass($this->value))->hasProperty($key);
        }

        return false;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @throws ReflectionException
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            return $default;
        }

        if ($this->isArray) {
            return $this->value[$key];
        }

        try {
            return $this->createReflectionProperty($key)->getValue($this->value);
        } catch (ReflectionException $e) {
            return $this->value->{$key};
        }
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->isArray) {
            $this->value[$key] = $value;
        }

        $this->overrideValue($key, $value);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        if ($this->isArray) {
            unset($this->value[$key]);
        }

        $this->overrideValue($key, null);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $name
     * @return bool
     * @throws ReflectionException
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @param $name
     * @return mixed|null
     * @throws ReflectionException
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @return void
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
     * @param mixed $offset
     * @return bool
     * @throws ReflectionException
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     * @throws ReflectionException
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
     * @param $key
     * @param $value
     */
    protected function overrideValue($key, $value)
    {
        if ($this->isObject) {
            try {
                $this->createReflectionProperty($key)->setValue($this->value, $value);
            } catch (ReflectionException $e) {
                $this->value->{$key} = $value;
            }
        }
    }

    /**
     * @param $name
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    protected function createReflectionProperty($name)
    {
        $property = new ReflectionProperty($this->value, $name);

        $property->setAccessible(true);

        return $property;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|void
     */
    public function __call($name, array $arguments = [])
    {
        if ($this->isObject && method_exists($this->value, $name)) {
            return $this->value->$name(...$arguments);
        }
    }
}