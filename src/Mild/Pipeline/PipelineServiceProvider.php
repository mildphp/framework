<?php

namespace Mild\Pipeline;

use Mild\Support\ServiceProvider;
use Mild\Contract\Pipeline\PipelineInterface;

class PipelineServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->alias(PipelineInterface::class, Pipeline::class);
    }
}