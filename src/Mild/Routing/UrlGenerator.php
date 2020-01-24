<?php

namespace Mild\Routing;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class UrlGenerator
{
    /**
     * @var UriInterface
     */
    protected $uri;
    /**
     * @var array
     */
    protected $routes;

    /**
     * UrlGenerator constructor.
     * @param UriInterface $uri
     * @param array $routes
     */
    public function __construct(UriInterface $uri, array $routes)
    {
        $this->uri = $uri;
        $this->routes = $routes;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param $path
     * @return UriInterface
     */
    public function to($path)
    {
        $basePath = $this->uri->getPath();

        if (($path = trim($path, '/'))) {
            return $this->uri->withPath($basePath.'/'.$path);
        }

        return $this->uri;
    }

    /**
     * @param $name
     * @param array $parameters
     * @return UriInterface
     */
    public function route($name, array $parameters = [])
    {
        if (!isset($this->routes[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Route [%s] does not exists', $name
            ));
        }

        $uri = $this->to(trim(preg_replace_callback('/\{(\w+?)\??}/', function ($matches) use (&$parameters) {

            if (isset($parameters[$matches[1]])) {
                $parameter = $parameters[$matches[1]];
                unset($parameters[$matches[1]]);

                return $parameter;
            }

            if (empty($parameter = array_shift($parameters)) && strpos($matches[0], '?') === false) {
                throw new InvalidArgumentException(sprintf(
                    'Parameters missing [%s]', $matches[1]
                ));
            }

            return $parameter;
        }, ($route = $this->routes[$name])->getPattern()), '/'));

        if (($host = $route->host())) {
            $uri = $uri->withHost($host);
        }

        return $uri;
    }
}