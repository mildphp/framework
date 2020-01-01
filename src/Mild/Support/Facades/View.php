<?php

namespace Mild\Support\Facades;

use Mild\View\Engine;
use Mild\Contract\View\CompilerInterface;
use Mild\Contract\View\RepositoryInterface;

/**
 * Class View
 *
 * @package \Mild\Support\Facades
 * @see \Mild\View\Factory
 * @method static Engine make($file, $data, $sections)
 * @method static RepositoryInterface getRepository()
 * @method static CompilerInterface getCompiler()
 */
class View extends Facade
{

    /**
     * @return string
     */
    protected static function getAccessor()
    {
        return 'view';
    }
}