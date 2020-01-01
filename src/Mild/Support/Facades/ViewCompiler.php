<?php

namespace Mild\Support\Facades;

/**
 * Class ViewCompiler
 *
 * @package \Mild\Support\Facades
 * @see \Mild\View\Compiler
 * @method static string getPath()
 * @method static string compile($contents)
 */
class ViewCompiler extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'view.compiler';
    }
}