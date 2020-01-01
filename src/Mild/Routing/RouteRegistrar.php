<?php

namespace Mild\Routing;

use Mild\Support\Arr;
use Mild\Contract\Routing\RouteRegistrarInterface;

/**
 * Class RouteRegistrar
 *
 * @package Mild\Routing
 * @method RouteRegistrar|array middleware(string|array|null $middleware = null)
 * @method RouteRegistrar|string name(string|null $name = null)
 * @method RouteRegistrar|string as(string|null $name = null)
 * @method RouteRegistrar|string host(string|null $host = null)
 * @method RouteRegistrar|string domain(string|null $domain = null)
 * @method RouteRegistrar|array where(array|string|null $where = null)
 * @method RouteRegistrar|string controller(string|null $controller = null)
 * @method RouteRegistrar|string namespace(string|null $namespace = null)
 * @method RouteRegistrar|string prefix(string|null $prefix = null)
 */
class RouteRegistrar extends RouteAttribute implements RouteRegistrarInterface
{
    /**
     * @var RouteCollection
     */
    protected $collection;
    /**
     * @var array
     */
    protected $allowAttributes = [
        'name' => 'name',
        'host' => 'host',
        'where' => 'where',
        'prefix' => 'prefix',
        'namespace' => 'namespace',
        'controller' => 'controller',
        'middleware' => 'middleware'
    ];
    /**
     * @var array
     */
    protected $aliasAttributes = [
        'as' => 'name',
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
     * RouteRegistrar constructor.
     *
     * @param RouteCollection $collection
     */
    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function get($pattern, $attributes = null)
    {
        return $this->registerRoute(['GET', 'HEAD'], $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function post($pattern, $attributes = null)
    {
        return $this->registerRoute('POST', $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function put($pattern, $attributes = null)
    {
        return $this->registerRoute('PUT', $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function delete($pattern, $attributes = null)
    {
        return $this->registerRoute('DELETE', $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function patch($pattern, $attributes = null)
    {
        return $this->registerRoute('PATCH', $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function options($pattern, $attributes = null)
    {
        return $this->registerRoute('OPTIONS', $pattern, $attributes);
    }

    /**
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    public function any($pattern, $attributes = null)
    {
        return $this->registerRoute(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], $pattern, $attributes);
    }

    /**
     * @param $resource
     * @return void
     */
    public function group($resource)
    {
        $this->collection->group($resource, $this->attributes);
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Menyatukan kondisi paramter dengan kondisi parameter yang baru.
     *
     * @param $value
     * @return array
     */
    protected function mergeWhere($value)
    {
        return array_merge($this->where(), Arr::wrap($value));
    }

    /**
     * Menyatukan prefix yang sudah ada dengan prefix yang baru
     *
     * @param $value
     * @return string
     */
    protected function mergePrefix($value)
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
    protected function mergeMiddleware($value)
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

    /**
     * @param $method
     * @param $pattern
     * @param null $attributes
     * @return Route
     */
    protected function registerRoute($method, $pattern, $attributes = null)
    {
        // Jika tipe data variabel $attributes yang anda berikan bukan array
        // maka kita mendeteksi itu adalah handle yang akan di gunakan di dalam route.
        if (!is_array($attributes)) {
            $attributes = ['controller' => $attributes];
        }

        $this->setAttributes($attributes);

        return tap($this->collection->createRoute($method, $pattern, $this->attributes), function ($route) {
            $this->collection->addRoute($route);
        });
    }
}