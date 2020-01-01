<?php

namespace Mild\Database\Query;

class Expression
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Expression constructor.
     *
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
}