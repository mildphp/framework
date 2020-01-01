<?php

namespace Mild\View;

use Mild\Support\Dot;

class Variable extends Dot
{
    /**
     * Variable constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->setItems($items);
    }

    /**
     * @param array $items
     * @return array
     */
    public function merge(array $items)
    {
        $this->mergeItems($items);

        return $this->getItems();
    }
}