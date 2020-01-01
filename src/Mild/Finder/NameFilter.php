<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Support\Arr;
use Mild\Contract\Finder\FilterInterface;

class NameFilter implements FilterInterface
{
    private $names;

    /**
     * NameFilter constructor.
     *
     * @param $names
     */
    public function __construct($names)
    {
        $this->names = Arr::wrap($names);
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return in_array(pathinfo($splFileInfo, PATHINFO_FILENAME), $this->names);
    }
}