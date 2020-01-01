<?php

namespace Mild\Support;

class MessageBag extends Dot
{
    /**
     * MessageBag constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->setItems($items);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function first($key)
    {
        return Arr::first($this->get($key));
    }

    /**
     * @param $key
     * @return mixed
     */
    public function last($key)
    {
        return Arr::last($this->get($key));
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->first($name);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->first($offset);
    }
}