<?php

namespace Mild\Support\Facades;

use Mild\Routing\RouteRegistrar;
use Mild\Contract\Routing\RouteInterface;

/**
 * Class Route
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Routing\RouteCollection
 * @method static void addRoute(RouteInterface $route)
 * @method static array getRoutes()
 * @method static void setRoutes($routes)
 * @method static void group($resource, $attributes)
 * @method static Route createRoute($method, $pattern, $attributes)
 * @method static RouteRegistrar get($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar post($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar put($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar patch($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar delete($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar options($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar any($pattern, array|mixed $attributes = null)
 * @method static RouteRegistrar|array middleware(string|array|null $middleware = null)
 * @method static RouteRegistrar|string name(string|null $name = null)
 * @method static RouteRegistrar|string as(string|null $name = null)
 * @method static RouteRegistrar|string host(string|null $host = null)
 * @method static RouteRegistrar|string domain(string|null $domain = null)
 * @method static RouteRegistrar|array where(array|string|null $where = null)
 * @method static RouteRegistrar|string controller(string|null $controller = null)
 * @method static RouteRegistrar|string namespace(string|null $namespace = null)
 * @method static RouteRegistrar|string prefix(string|null $prefix = null)
 */
class Route extends Facade
{

    /**
     * @return string
     */
    protected static function getAccessor()
    {
        return 'router';
    }
}