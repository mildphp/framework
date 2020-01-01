<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Contract\Finder\FilterInterface;

class ReadableFilter implements FilterInterface
{

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return $splFileInfo->isReadable();
    }
}