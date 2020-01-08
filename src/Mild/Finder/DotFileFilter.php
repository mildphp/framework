<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Contract\Finder\FilterInterface;

class DotFileFilter implements FilterInterface
{
    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return !empty(pathinfo($splFileInfo, PATHINFO_FILENAME));
    }
}
