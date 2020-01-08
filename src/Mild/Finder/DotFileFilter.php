<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Support\Arr;
use Mild\Contract\Finder\FilterInterface;

class DotFileFilter implements FilterInterface
{
    /**
     * @var array|null
     */
    private $names;

    /**
     * @var mixed|null $names
     */
    public function __construct($names = null)
    {
        if ($names) {
            $this->names = Arr::wrap($names);
        }
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        if ($this->names) {
            return in_array($splFileInfo->getFilename(), $this->names);
        }

        return !empty(pathinfo($splFileInfo, PATHINFO_FILENAME));
    }
}
