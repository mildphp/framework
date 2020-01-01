<?php

namespace Mild\Finder;

use SplFileInfo;
use Mild\Contract\Finder\FilterInterface;

class CallableFilter implements FilterInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * CallableFilter constructor.
     *
     * @param $callable
     */
    public function __construct($callable)
    {
        $this->callable = $callable;
    }


    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        return call_user_func($this->callable, $splFileInfo);
    }
}