<?php

namespace Mild\Support;

class Collection extends Dot
{
    /**
     * Collection constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->setItems($items);
    }

    /**
     * @param callable $callback
     * @return Collection
     */
    public function map(callable $callback)
    {
        return new self(array_map($callback, $this->items, array_keys($this->items)));
    }

    /**
     * @param callable $callback
     * @return Collection
     */
    public function filter(callable $callback)
    {
        return new self(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return Arr::first($this->items);
    }
}