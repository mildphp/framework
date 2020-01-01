<?php

namespace Mild\Contract\Http;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;

interface FactoryInterface
{
    /**
     * @param $method
     * @param $uri
     * @return RequestInterface
     */
    public static function createRequest($method, $uri);

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public static function createResponse($code = 200, $reasonPhrase = '');

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $serverParams
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public static function createServerRequest($method, $uri, $serverParams = []);

    /**
     * @param string $content
     * @return StreamInterface
     */
    public static function createStream($content = '');

    /**
     * @param string $filename
     * @param string $mode
     * @return StreamInterface
     */
    public static function createStreamFromFile($filename, $mode = 'r');

    /**
     * @param $resource
     * @return StreamInterface
     */
    public static function createStreamFromResource($resource);

    /**
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFileInterface
     */
    public static function createUploadedFile(StreamInterface $stream, $size = 0, $error = UPLOAD_ERR_OK, $clientFilename = '', $clientMediaType = '');

    /**
     * @param string $uri
     * @return UriInterface
     */
    public static function createUri($uri = '');

    /**
     * @return UriInterface
     */
    public static function createUriFromGlobals();

    /**
     * @return ServerRequestInterface
     */
    public static function createServerRequestFromGlobals();
}