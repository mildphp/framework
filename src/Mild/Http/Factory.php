<?php

namespace Mild\Http;

use Mild\Contract\Http\StreamInterface;
use Mild\Contract\Http\FactoryInterface;

class Factory implements FactoryInterface
{

    /**
     * @param string $method
     * @param Uri|string $uri
     * @return Request
     */
    public static function createRequest($method, $uri)
    {
        return new Request(
            $method,
            $uri instanceof Uri ? $uri : self::createUri($uri),
            self::createStream()
        );
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return Response
     */
    public static function createResponse($code = 200, $reasonPhrase = '')
    {
        return new Response(
            self::createStream(),
            $code,
            $reasonPhrase
        );
    }

    /**
     * @param string $method
     * @param Uri|string $uri
     * @param array $serverParams
     * @return ServerRequest
     */
    public static function createServerRequest($method, $uri, $serverParams = [])
    {
        return new ServerRequest(
            ($method === '' && isset($serverParams['REQUEST_METHOD'])) ? $serverParams['REQUEST_METHOD'] : $method,
            $uri instanceof Uri ? $uri : self::createUri($uri),
            self::createStream(),
            $serverParams
        );
    }

    /**
     * @return ServerRequest
     */
    public static function createServerRequestFromGlobals()
    {
        $files = [];
        $method = 'GET';
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $stream = self::createStream();
        $stream->copy($resource = self::createStreamFromFile('php://input'));
        $resource->close();
        foreach ($_FILES as $key => $value) {
            if (is_array($value['error'])) {
                foreach ($value['error'] as $k => $v) {
                    if ($value['tmp_name'] === '') {
                        continue;
                    }
                    $files[$key][$k] = new UploadedFile(
                        self::createStreamFromFile($value['tmp_name'][$k]),
                        $value['size'][$k],
                        $value['error'][$k],
                        $value['name'][$k],
                        $value['type'][$k]
                    );
                }
                continue;
            }
            if ($value['tmp_name'] === '') {
                continue;
            }
            $files[$key] = new UploadedFile(
                self::createStreamFromFile($value['tmp_name']),
                $value['size'],
                $value['error'],
                $value['name'],
                $value['type']
            );
        }
        return new ServerRequest(
            $method,
            self::createUriFromGlobals(),
            $stream,
            $_SERVER,
            $_POST,
            $_GET,
            $_COOKIE,
            $files,
            getallheaders()
        );
    }

    /**
     * @param string $content
     * @return Stream
     */
    public static function createStream($content = '')
    {
        $stream = self::createStreamFromFile('php://temp', 'r+');
        $stream->write($content);
        return $stream;
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return Stream
     */
    public static function createStreamFromFile($filename, $mode = 'r')
    {
        return self::createStreamFromResource(fopen($filename, $mode));
    }

    /**
     * @param resource $resource
     * @return Stream
     */
    public static function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }

    /**
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFile
     */
    public static function createUploadedFile(StreamInterface $stream, $size = 0, $error = UPLOAD_ERR_OK, $clientFilename = '', $clientMediaType = '')
    {
        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * @param string $uri
     * @return Uri
     */
    public static function createUri($uri = '')
    {
        $parts = parse_url($uri);
        $scheme = '';
        $host = '';
        $port = null;
        $path = '/';
        $query = '';
        $fragment = '';
        $user = '';
        $pass = '';
        if (isset($parts['scheme'])) {
            $scheme = $parts['scheme'];
        }
        if (isset($parts['host'])) {
            $host = $parts['host'];
        }
        if (isset($parts['port'])) {
            $port = $parts['port'];
        }
        if (isset($parts['path']) && $parts['path'] !== '') {
            $path = $parts['path'];
        }
        if (isset($parts['query'])) {
            $query = $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $fragment = $parts['fragment'];
        }
        if (isset($parts['user'])) {
            $user = $parts['user'];
        }
        if (isset($parts['pass'])) {
            $pass = $parts['pass'];
        }
        return new Uri($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /**
     * @return Uri
     */
    public static function createUriFromGlobals()
    {
        $host = 'localhost';
        $scheme = 'http';
        $port = null;
        $path = '/';
        $query = '';
        $user = '';
        $pass = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = 'https';
        }
        if (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        }
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = (int) $_SERVER['SERVER_PORT'];
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = $_SERVER['REQUEST_URI'];

            // Jika di dalam url terdapat query, maka kita akan menghilangkan
            // query tersebut.
            if (($queryIndex = strpos($path, '?')) !== false) {
                $path = substr($path, 0, $queryIndex);
            }
        }
        if (isset($_SERVER['QUERY_STRING'])) {
            $query = $_SERVER['QUERY_STRING'];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $_SERVER['PHP_AUTH_USER'];
        }
        if (isset($_SERVER['PHP_AUTH_PW'])) {
            $pass = $_SERVER['PHP_AUTH_PW'];
        }

        return new Uri($scheme, $host, $port, $path, $query, '', $user, $pass);
    }
}