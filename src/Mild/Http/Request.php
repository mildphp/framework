<?php

namespace Mild\Http;

use Mild\Support\Arr;
use Mild\Support\Traits\Macroable;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Mild\Contract\Http\StreamInterface;

class Request implements RequestInterface
{
    use Macroable, MessageTrait;

    /**
     * @var UriInterface
     */
    protected $uri;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $requestTarget;

    /**
     * Request constructor.
     *
     * @param $method
     * @param UriInterface $uri
     * @param StreamInterface $body
     * @param array $headers
     * @param string $protocolVersion
     */
    public function __construct(
        $method,
        UriInterface $uri,
        StreamInterface $body,
        array $headers = [],
        $protocolVersion = '1.0'
    )
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->body = $body;
        $this->setHeaders($headers);
        $this->assertProtocolVersion($protocolVersion);
        $this->protocolVersion = $protocolVersion;
    }

    /**
     * @return string
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }
        $this->requestTarget = $this->uri->getPath();
        if ($this->requestTarget === '') {
            $this->requestTarget = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $this->requestTarget .= '?' . $this->uri->getQuery();
        }
        return $this->requestTarget;
    }

    /**
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        $this->assertWhiteSpace($requestTarget);
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return static
     */
    public function withMethod($method)
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);
        return $clone;
    }

    /**
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $clone = clone $this;
        $clone->uri = $uri;
        if (($host = $uri->getHost()) !== '') {
            if (($port = $uri->getPort()) !== null) {
                $host .= ':'.$port;
            }
            if (!$preserveHost) {
                $clone->headers['Host'] = Arr::wrap($host);
                $clone->headerNames['host'] = 'Host';
            } elseif (!isset($this->headerNames['host'])) {
                $clone->headers['Host'] = Arr::wrap($host);
                $clone->headerNames['host'] = 'Host';
            }
        }
        return $clone;
    }
}