<?php

namespace Mild\Routing;

use Mild\Support\Arr;

/**
 * Class RouteGroup
 *
 * @package Mild\Routing
 * @method RouteGroup|array middleware(string|array|null $middleware = null)
 * @method RouteGroup|string host(string|null $host = null)
 * @method RouteGroup|string domain(string|null $domain = null)
 * @method RouteGroup|array where(array|string|null $where = null)
 * @method RouteGroup|string namespace(string|null $namespace = null)
 * @method RouteGroup|string prefix(string|null $prefix = null)
 */
class RouteGroup extends RouteAttribute
{
    /**
     * @var array
     */
    protected $allowAttributes = [
        'host' => 'host',
        'where' => 'where',
        'prefix' => 'prefix',
        'namespace' => 'namespace',
        'middleware' => 'middleware'
    ];
    /**
     * @var array
     */
    protected $aliasAttributes = [
        'domain' => 'host'
    ];
    /**
     * @var array
     */
    protected $mergeValueAttributes = [
        'where' => 'mergeWhere',
        'prefix' => 'mergePrefix',
        'namespace' => 'mergeNameSpace',
        'middleware' => 'mergeMiddleware'
    ];
    /**
     * @var array
     */
    protected $defaultValueAttributes = [
        'host' => '',
        'where' => [],
        'prefix' => '',
        'namespace' => '',
        'middleware' => []
    ];

    /**
     * Muat router yang harus di muat oleh group.
     *
     * @param RouteCollection $router
     * @param string|callable $resource
     * @return void
     */
    public function load(RouteCollection $router, $resource)
    {
        if (is_callable($resource)) {
            $resource($router);
        } else {
            require ''.$resource.'';
        }
    }

    /**
     * Menyatukan kondisi paramter dengan kondisi parameter yang baru.
     *
     * @param $value
     * @return array
     */
    public function mergeWhere($value)
    {
        return array_merge($this->where(), Arr::wrap($value));
    }

    /**
     * Menyatukan prefix yang sudah ada dengan prefix yang baru
     *
     * @param $value
     * @return string
     */
    public function mergePrefix($value)
    {
        $value = trim($value, '/');
        $prefix = $this->prefix();
        if ($prefix === '') {
            return '/'.$value;
        }
        if ($value === '') {
            return $prefix;
        }
        return $prefix.'/'.$value;
    }

    /**
     * Menyatukan middleware yang sudah dengan middleware yang baru.
     *
     * @param $value
     * @return mixed
     */
    public function mergeMiddleware($value)
    {
        return array_merge($this->middleware(), Arr::wrap($value));
    }

    /**
     * Menyatukan namespace yang sudah ada dengan namespace yang baru.
     *
     * @param $value
     * @return string
     */
    protected function mergeNameSpace($value)
    {
        $value = trim($value, '\\');
        $namespace = $this->namespace();

        if ($namespace === '' || $value === '') {
            return $value;
        }

        return $namespace.'\\'.$value;
    }
}