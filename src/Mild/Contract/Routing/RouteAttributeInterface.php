<?php

namespace Mild\Contract\Routing;

interface RouteAttributeInterface
{
    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes);

    /**
     * @param $name
     * @param array $arguments
     * @return static|mixed|null
     */
    public function __call($name, array $arguments = []);
}