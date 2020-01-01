<?php

namespace Mild\Contract\Finder;

use SplFileInfo;

interface FilterInterface
{
    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo);
}