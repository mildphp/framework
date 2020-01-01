<?php

namespace Mild\Contract\Routing;

interface RouteCollectionInterface
{
    /**
     * @return array
     */
    public function getRoutes();

    /**
     * @param array $routes
     * @return void
     */
    public function setRoutes(array $routes);

    /**
     * @param RouteInterface $route
     * @return void
     */
    public function addRoute(RouteInterface $route);

    /**
     * @param $name
     * @param array $arguments
     * @return RouteRegistrarInterface|RouteInterface
     */
    public function __call($name, array $arguments = []);
}