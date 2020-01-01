<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Contract\Finder\FilterInterface;

class SizeFilter implements FilterInterface
{
    /**
     * @var int|string
     */
    private $size;
    /**
     * @var null
     */
    private $operator;

    /**
     * SizeFilter constructor.
     *
     * @param $size
     * @param null $operator
     */
    public function __construct($size, $operator = null)
    {
        $this->size = (int) $size;
        $this->operator = $operator;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        $size = $splFileInfo->getSize();
        switch ($this->operator) {
            case '<':
                return $size < $this->size;
                break;
            case '>':
                return $size > $this->size;
                break;
            case '<=':
                return $size <= $this->size;
                break;
            case '>=':
                return $size >= $this->size;
                break;
            case '!=':
                return $size != $this->size;
                break;
            default:
                return $size === $this->size;
                break;
        }
    }
}