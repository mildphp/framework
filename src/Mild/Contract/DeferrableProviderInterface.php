<?php

namespace Mild\Contract;

interface DeferrableProviderInterface
{
    /**
     * @return string|array
     */
    public function provides();
}