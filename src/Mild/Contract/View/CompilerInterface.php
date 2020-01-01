<?php

namespace Mild\Contract\View;

interface CompilerInterface
{
    /**
     * @return string
     */
    public function getPath();

    /**
     * @param $contents
     * @return string
     */
    public function compile($contents);
}