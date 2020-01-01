<?php

namespace Mild\Finder;

use SplFileInfo;

class NotNameFilter extends NameFilter
{
    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return false === parent::accept($splFileInfo);
    }
}