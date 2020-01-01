<?php

namespace Mild\Finder;

use SplFileInfo;

class NotExtensionFilter extends ExtensionFilter
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