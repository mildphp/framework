<?php

namespace Mild\Routing;

use Mild\Support\ServiceProvider;
use Mild\Contract\Routing\RouteCollectionInterface;

abstract class RouteServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('router', RouteCollection::class);
        $this->application->alias(RouteCollectionInterface::class, 'router');
    }

    /**
     * @return void
     */
    public function boot()
    {
        if (is_file($file = $this->application->get('route.cache.path'))) {
            require ''.$file.'';
        } else {
            $this->mapRoute();
        }
    }

    /**
     * @return void
     */
    abstract protected function mapRoute();
}