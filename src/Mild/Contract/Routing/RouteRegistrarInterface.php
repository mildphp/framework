<?php

namespace Mild\Contract\Routing;

interface RouteRegistrarInterface extends RouteAttributeInterface
{
    /**
     * @return RouteCollectionInterface
     */
    public function getCollection();

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function get($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function post($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function put($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function delete($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function patch($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function options($pattern, $attributes = null);

    /**
     * @param $pattern
     * @param null $attributes
     * @return RouteInterface
     */
    public function any($pattern, $attributes = null);
}