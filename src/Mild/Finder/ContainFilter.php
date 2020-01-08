<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Support\Str;
use Mild\Contract\Finder\FilterInterface;

class ContainFilter implements FilterInterface
{
    /**
     * @var mixed
     */
    private $needles;

    /**
     * ContainFilter constructor.
     *
     * @param array $needles
     */
    public function __construct($needles)
    {
        $this->needles = $needles;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return Str::contains($splFileInfo->getFilename(), $this->needles);
    }
}
