<?php

namespace Mild\Contract\Http;

use Mild\Contract\Routing\RouteInterface;
use Psr\Http\Message\ServerRequestInterface as PsrServerRequestInterface;

interface ServerRequestInterface extends PsrServerRequestInterface
{
    /**
     * @return bool
     */
    public function isXhr();

    /**
     * @return bool
     */
    public function isXml();

    /**
     * @return bool
     */
    public function isJson();

    /**
     * @return bool
     */
    public function isPlain();

    /**
     * @return RouteInterface|null
     */
    public function getRoute();

    /**
     * @param RouteInterface $route
     * @return static
     */
    public function withRoute(RouteInterface $route);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getCookieParam($key, $default = null);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getQueryParam($key, $default = null);

    /**
     * @param $key
     * @param null $default
     * @return UploadedFileInterface|null
     */
    public function getUploadedFile($key, $default = null);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getServerParam($key, $default = null);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getParsedBodyParam($key, $default = null);
}