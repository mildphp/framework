<?php

namespace Mild\Http;

use Psr\Http\Message\UriInterface;
use Mild\Contract\Http\StreamInterface;
use Mild\Contract\Routing\RouteInterface;
use Mild\Contract\Http\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var RouteInterface|null
     */
    protected $route;
    /**
     * @var array
     */
    protected $parsedBody;
    /**
     * @var array
     */
    protected $queryParams;
    /**
     * @var array
     */
    protected $serverParams;
    /**
     * @var array
     */
    protected $cookieParams;
    /**
     * @var array
     */
    protected $uploadedFiles;
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * ServerRequest constructor.
     *
     * @param $method
     * @param UriInterface $uri
     * @param StreamInterface $stream
     * @param array $serverParams
     * @param array $parsedBody
     * @param array $queryParams
     * @param array $cookieParams
     * @param array $uploadedFiles
     * @param array $headers
     * @param string $protocolVersion
     */
    public function __construct(
        $method,
        UriInterface $uri,
        StreamInterface $stream,
        array $serverParams = [],
        array $parsedBody = [],
        array $queryParams = [],
        array $cookieParams = [],
        array $uploadedFiles = [],
        array $headers = [],
        $protocolVersion = '1.0'
    )
    {
        $this->serverParams = $serverParams;
        $this->parsedBody = $parsedBody;
        $this->queryParams = $queryParams;
        $this->cookieParams = $cookieParams;
        $this->uploadedFiles = $uploadedFiles;
        parent::__construct($method, $uri, $stream, $headers, $protocolVersion);
    }

    /**
     * @return RouteInterface|null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getServerParam($key, $default = null)
    {
        if (!isset($this->serverParams[$key])) {
            return $default;
        }
        return $this->serverParams[$key];
    }

    /**
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getCookieParam($key, $default = null)
    {
        if (!isset($this->cookieParams[$key])) {
            return $default;
        }
        return $this->cookieParams[$key];
    }

    /**
     * @param RouteInterface $route
     * @return static
     */
    public function withRoute(RouteInterface $route)
    {
        $clone = clone $this;

        $clone->route = $route;

        return $clone;
    }

    /**
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getQueryParam($key, $default = null)
    {
        if (!isset($this->queryParams[$key])) {
            return $default;
        }
        return $this->queryParams[$key];
    }

    /**
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    /**
     * @return array
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param $key
     * @param null $default
     * @return UploadedFile|array|null
     */
    public function getUploadedFile($key, $default = null)
    {
        if (!isset($this->uploadedFiles[$key])) {
            return $default;
        }
        return $this->uploadedFiles[$key];
    }

    /**
     * @param array $uploadedFiles
     * @return static
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;
        return $clone;
    }

    /**
     * @return array
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getParsedBodyParam($key, $default = null)
    {
        if (!isset($this->parsedBody[$key])) {
            return $default;
        }
        return $this->parsedBody[$key];
    }

    /**
     * @param array $data
     * @return static
     */
    public function withParsedBody($data)
    {
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getAttribute($name, $default = null)
    {
        if (!isset($this->attributes[$name])) {
            return $default;
        }
        return $this->attributes[$name];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }

    /**
     * @return bool
     */
    public function isXhr()
    {
        return strtolower($this->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * @return bool
     */
    public function isXml()
    {
        return strpos(strtolower($this->getHeaderLine('Content-Type')), 'xml') !== false;
    }

    /**
     * @return bool
     */
    public function isJson()
    {
        return strpos($this->getHeaderLine('Content-Type'), 'json') !== false;
    }

    /**
     * @return bool
     */
    public function isPlain()
    {
        return strpos(strtolower($this->getHeaderLine('Content-Type')), 'plain') !== false;
    }
}