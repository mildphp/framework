<?php

namespace Mild\Finder;

use SplFileInfo;

class NotContainFilter extends ContainFilter
{
    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return parent::accept($splFileInfo) === false;
    }
}