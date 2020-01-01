<?php

namespace Mild\Validation;

use Mild\Support\Dot;
use Mild\Contract\Validation\GatherDataInterface;

class GatherData extends Dot implements GatherDataInterface
{
    /**
     * GatherData constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->setItems($items);
    }
}