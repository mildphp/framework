<?php

namespace Mild\Routing;

use Mild\Support\Arr;
use Mild\Contract\Routing\RouteInterface;
use Mild\Contract\Routing\RouteCollectionInterface;

/**
 * Class RouteCollection
 *
 * @package Mild\Routing
 * @method RouteRegistrar get($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar post($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar put($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar patch($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar delete($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar options($pattern, array|mixed $attributes = null)
 * @method RouteRegistrar any($pattern, array|mixed $attributes = null)
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
class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var array
     */
    protected $routes = [];
    /**
     * @var RouteGroup
     */
    private $routeGroup;

    /**
     * RouteCollection constructor.
     *
     * @param array $routes
     */
    public function __construct($routes = [])
    {
        $this->setRoutes($routes);
        $this->routeGroup = new RouteGroup;
    }

    /**
     * @param RouteInterface $route
     * @return void
     */
    public function addRoute(RouteInterface $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     * @return void
     */
    public function setRoutes(array $routes)
    {
        foreach ($routes as $route) {
            $this->addRoute($route);
        }
    }

    /**
     * @param string|callable $resource
     * @param array $attributes
     * @return void
     */
    public function group($resource, array $attributes = [])
    {
        $routeGroup = clone $this->routeGroup;

        $this->routeGroup->setAttributes($attributes);

        $this->routeGroup->load($this, $resource);
        
        $this->routeGroup = $routeGroup;
    }

    /**
     * @param $method
     * @param $pattern
     * @param array $attributes
     * @return Route
     */
    public function createRoute($method, $pattern, array $attributes = [])
    {
        // Jika anda memberikan Method dengan tipe data selain array, maka kita akan
        // merubah tipe data menjadi tipe data array.
        $method = Arr::wrap($method);

        // Jika indeks use dalam attributes itu adalah tipe data string atau array
        // maka kita akan menambahkan namespace di dalamnya.
        if (isset($attributes['controller'])) {
            $attributes['controller'] = $this->resolveUse($attributes['controller']);
        }

        // Jika anda tidak mendefinisikan host di dalam route, dan sebelumnya
        // anda mendefinisikan host di dalam route group, maka secara automatis
        // kita akan menambahkan host yang ada di dalam route ke dalam route.
        if (!isset($attributes['host'])) {
            $attributes['host'] = $this->routeGroup->host();
        }

        // Jika sebelumnya anda menambahkan kondisi regex di dalam group, maka kita akan
        // menyatukan kondisi regex yang sudah anda definisikan di dalam group dan di route.
        if (isset($attributes['where'])) {
            $attributes['where'] = $this->routeGroup->mergeWhere($attributes['where']);
        } else {
            $attributes['where'] = $this->routeGroup->where();
        }

        // Jika sebelumnya anda menambahkan middleware di dalam group, maka kita akan
        // menyatukan middleware yang sudah anda definisikan di dalam group dan di route.
        if (isset($attributes['middleware'])) {
            $attributes['middleware'] = $this->routeGroup->mergeMiddleware($attributes['middleware']);
        } else {
            $attributes['middleware'] = $this->routeGroup->middleware();
        }

        return new Route(
            $this->routeGroup->mergePrefix($pattern), $method, $attributes
        );
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        return (new RouteRegistrar($this))->$name(...$arguments);
    }

    /**
     * @param $use
     * @return array|string
     */
    protected function resolveUse($use)
    {
        if (is_string($use) && ($namespace = $this->routeGroup->namespace()) !== '' && ($use = trim($use, '\\') ) !== '') {
            return $namespace.'\\'.$use;
        }

        if (is_array($use) && isset($use[0])) {
            $use[0] = $this->resolveUse($use[0]);
        }

        return $use;
    }
}