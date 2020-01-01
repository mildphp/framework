<?php

namespace Mild\Support\Facades;

use Mild\Http\UploadedFile;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Mild\Contract\Routing\RouteInterface;

/**
 * Class Request
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Http\ServerRequest
 * @method static RouteInterface|null getRoute()
 * @method static array getServerParams()
 * @method static mixed|null getServerParam($key, $default = null)
 * @method static array getCookieParams()
 * @method static mixed|null getCookieParam($key, $default = null)
 * @method static static withRoute(RouteInterface $route)
 * @method static static withCookieParams($cookies)
 * @method static array getQueryParams()
 * @method static mixed|null getQueryParam($key, $default = null)
 * @method static static withQueryParams($query)
 * @method static array getUploadedFiles()
 * @method static UploadedFile|array|null getUploadedFile($key, $default = null)
 * @method static static withUploadedFiles($uploadedFiles)
 * @method static array getParsedBody()
 * @method static mixed|null getParsedBodyParam($key, $default = null)
 * @method static static withParsedBody($data)
 * @method static array getAttributes()
 * @method static mixed|null getAttribute($name, $default = null)
 * @method static static withAttribute($name, $value)
 * @method static static withoutAttribute($name)
 * @method static bool isXhr()
 * @method static bool isXml()
 * @method static bool isJson()
 * @method static bool isPlain()
 * @method static string getRequestTarget()
 * @method static static withRequestTarget($requestTarget)
 * @method static string getMethod()
 * @method static static withMethod($method)
 * @method static UriInterface getUri()
 * @method static static withUri(UriInterface $uri, bool $preserveHost = false)
 * @method static string getProtocolVersion()
 * @method static static withProtocolVersion($version)
 * @method static array getHeaders()
 * @method static bool hasHeader($name)
 * @method static array getHeader($name)
 * @method static string getHeaderLine($name)
 * @method static static withHeader($name, $value)
 * @method static static withAddedHeader($name, $value)
 * @method static static withoutHeader($name)
 * @method static StreamInterface getBody()
 * @method static static withBody(StreamInterface $body)
 */
class Request extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'request';
    }
}