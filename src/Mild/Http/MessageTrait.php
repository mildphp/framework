<?php

namespace Mild\Http;

use Mild\Support\Arr;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

trait MessageTrait
{
    /**
     * @var StreamInterface
     */
    protected $body;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $protocolVersion;
    /**
     * @var array
     */
    private $headerNames = [];

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param $version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $this->assertProtocolVersion($version);
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return isset($this->headerNames[$this->normalizeHeaderName($name)]);
    }

    /**
     * @param $name
     * @return array
     */
    public function getHeader($name)
    {
        if (!isset($this->headerNames[$name = $this->normalizeHeaderName($name)])) {
            return [];
        }
        return $this->headers[$this->headerNames[$name]];
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param $name
     * @param $value
     * @return static
     */
    public function withHeader($name, $value)
    {
        $this->assertWhiteSpace($name);
        $clone = clone $this;
        if (isset($this->headerNames[$normalized = $this->normalizeHeaderName($name)])) {
            $name = $this->headerNames[$normalized];
        } else {
            $clone->headerNames[$normalized] = $name;
        }
        $clone->headers[$name] = Arr::wrap($value);
        return $clone;
    }

    /**
     * @param $name
     * @param $value
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        return $this->withHeader($name, array_merge($this->getHeader($name), Arr::wrap($value)));
    }

    /**
     * @param $name
     * @return static
     */
    public function withoutHeader($name)
    {
        $clone = clone $this;
        if (isset($this->headerNames[$normalized = $this->normalizeHeaderName($name)])) {
            $name = $this->headerNames[$normalized];
        }
        unset($clone->headers[$name], $clone->headerNames[$normalized]);
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * @param array $headers
     * @return void
     */
    protected function setHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->assertWhiteSpace($key);
            $this->headers[$key] = Arr::wrap($value);
            $this->headerNames[$this->normalizeHeaderName($key)] = $key;
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function normalizeHeaderName($name)
    {
        return str_replace('_', '-', strtolower($name));
    }

    /**
     * @param $value
     * @return void
     */
    protected function assertWhiteSpace($value)
    {
        if (strpos($value, ' ') !== false) {
            throw new InvalidArgumentException(sprintf(
                'The [%s] cannot contains the whitespace.', $value
            ));
        }
    }

    /**
     * @param $version
     * @return void
     */
    protected function assertProtocolVersion($version)
    {
        if (!is_string($version) || $version !== '1.0' && $version !== '1.1' && $version !== '2.0' && $version !== '2') {
            throw new InvalidArgumentException('The protocol version is invalid.');
        }
    }
}