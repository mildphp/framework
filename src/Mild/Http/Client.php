<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mild\Contract\Http\ClientInterface;
use Mild\Contract\Pipeline\PipelineInterface;
use Mild\Contract\Http\ClientHandlerInterface;

/**
 * Class Client
 *
 * @package Mild\Http
 * @method Response get($url, callable|null $callback = null)
 * @method Response post($url, callable|null $callback = null)
 * @method Response put($url, callable|null $callback = null)
 * @method Response patch($url, callable|null $callback = null)
 * @method Response delete($url, callable|null $callback = null)
 * @method Response options($url, callable|null $callback = null)
 */
class Client implements ClientInterface
{
    /**
     * @var ClientHandlerInterface
     */
    protected $handler;
    /**
     * @var PipelineInterface
     */
    protected $pipeline;

    /**
     * Client constructor.
     *
     * @param PipelineInterface $pipeline
     * @param ClientHandlerInterface|null $handler
     */
    public function __construct(PipelineInterface $pipeline, ClientHandlerInterface $handler = null)
    {
        $this->pipeline = $pipeline;

        if (null === $handler) {
            $handler = extension_loaded('curl') ? new CurlClientHandler : new StreamClientHandler;
        }

        $this->handler = $handler;
    }

    /**
     * @param $method
     * @param $url
     * @param callable|null $callback
     * @return ResponseInterface
     * @throws RequestException
     */
    public function request($method, $url, callable $callback = null)
    {
        $request = Factory::createRequest($method, $url);

        if (null !== $callback) {
            $request = $callback($request, $this->handler);
        }

        return $this->sendRequest($request);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws RequestException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->pipeline->addPipe(ClientRedirectMiddleware::class);

        $response = $this->pipeline->send(function ($request) {
            return $this->handler->handle($request);
        }, $request);

        if ($response->getStatusCode() >= 400) throw new RequestException($request, $response);

        return $response;

    }

    /**
     * @return ClientHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return PipelineInterface
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * @param $name
     * @param array $arguments
     * @return ResponseInterface
     * @throws RequestException
     */
    public function __call($name, array $arguments = [])
    {
        return $this->request($name, ...$arguments);
    }
}