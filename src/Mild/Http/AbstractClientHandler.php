<?php

namespace Mild\Http;

use Mild\Support\Collection;
use Mild\Contract\Http\ClientHandlerInterface;

abstract class AbstractClientHandler implements ClientHandlerInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * AbstractClientHandler constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->collection = new Collection($options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->collection->all();
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasOption($key)
    {
        return $this->collection->has($key);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getOption($key, $default = null)
    {
        return $this->collection->get($key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setOption($key, $value)
    {
        $this->collection->set($key, $value);
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function normalizeHeaders(array $headers)
    {
        $parts = [];

        foreach ($headers as $key => $value) {
            $parts[] = sprintf('%s: %s',$key, implode(', ', $value));
        }

        return $parts;
    }

    /**
     * @param array $headers
     * @param $body
     * @return Response
     */
    protected function createResponse(array $headers, $body)
    {
        $response = Factory::createResponse(200);

        $response->getBody()->write($body);

        [$protocolVersion, $statusCode, $reasonPhrase] = explode(' ', array_shift($headers), 3);

        $response = $response->withStatus($statusCode, $reasonPhrase)->withProtocolVersion(substr($protocolVersion, strpos($protocolVersion, '/') + 1));

        foreach ($headers as $header) {
            [$key, $value] = explode(': ', $header);
            $response = $response->withHeader($key, $value);
        }

        return $response;
    }
}