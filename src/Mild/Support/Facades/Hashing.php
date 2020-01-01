<?php

namespace Mild\Support\Facades;

class Hashing extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor()
    {
        return 'hashing';
    }
}