<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Support\Arr;
use Mild\Contract\Finder\FilterInterface;

class ExtensionFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $extensions;

    /**
     * ExtensionFilter constructor.
     *
     * @param $extensions
     */
    public function __construct($extensions)
    {
        $this->extensions = Arr::wrap($extensions);
    }


    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return in_array($splFileInfo->getExtension(), $this->extensions);
    }
}