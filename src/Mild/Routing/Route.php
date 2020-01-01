<?php

namespace Mild\Routing;

use Mild\Support\Arr;
use Mild\Contract\Routing\RouteInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * Class Route
 *
 * @package Mild\Routing
 * @method Route|array middleware(string|array|null $middleware = null)
 * @method Route|string name(string|null $name = null)
 * @method Route|string as(string|null $name = null)
 * @method Route|string host(string|null $host = null)
 * @method Route|string domain(string|null $domain = null)
 * @method Route|array where(array|string|null $where = null)
 * @method Route|string controller(mixed|null $controller = null)
 */
class Route extends RouteAttribute implements RouteInterface
{
    /**
     * @var array
     */
    protected $methods;
    /**
     * @var string
     */
    protected $pattern;
    /**
     * @var array
     */
    protected $parameters = [];
    /**
     * @var array
     */
    protected $allowAttributes = [
        'name' => 'name',
        'host' => 'host',
        'where' => 'where',
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
        'middleware' => 'mergeMiddleware'
    ];
    /**
     * @var array
     */
    protected $defaultValueAttributes = [
        'host' => '',
        'where' => [],
        'middleware' => []
    ];

    /**
     * Route constructor.
     *
     * @param $pattern
     * @param array $methods
     * @param array $attributes
     */
    public function __construct($pattern, array $methods, $attributes = [])
    {
        $this->pattern = $pattern;
        $this->methods = $methods;
        $this->setAttributes($attributes);
    }

    /**
     * Menyesuaikan url yang di request dengan pattern yang terdapat di route.
     *
     * @param $host
     * @param $path
     * @return bool
     */
    public function match($host, $path)
    {
        preg_match_all('/\{(\w+?)\?\}/', $this->pattern, $matches);

        $compiler = (new SymfonyRoute(
            preg_replace('/\{(\w+?)\?\}/','{$1}', $this->pattern),
            isset($matches[1]) ? array_fill_keys($matches[1], null) : [],
            $this->where(),
            ['utf8' => true],
            $this->host()
        ))->compile();

        if (($regex = $compiler->getHostRegex())) {
            if (empty($matches = $this->matchesRegex($regex, $host))) {
                return false;
            }
            $this->parameters = $this->filterMatches($matches);
        }

        if (empty($matches = $this->matchesRegex($compiler->getRegex(), rawurldecode($path)))) {
            return false;
        }

        $this->parameters += $this->filterMatches($matches);

        return true;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getParameter($key, $default = null)
    {
        if (!isset($this->parameters[$key])) {
            return $default;
        }
        return $this->parameters[$key];
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
     * @param $regex
     * @param $input
     * @return array
     */
    protected function matchesRegex($regex, $input)
    {
        preg_match($regex, $input, $matches);

        return $matches;
    }

    /**
     * @param $matches
     * @return array
     */
    protected function filterMatches($matches)
    {
        return array_filter(array_slice($matches, 1), 'is_string', ARRAY_FILTER_USE_KEY);
    }
}