<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\RequestExceptionInterface;

class RequestException extends NetworkException implements RequestExceptionInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * RequestException constructor.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct($request, sprintf('%s %s (%s)', $response->getStatusCode(), $response->getReasonPhrase(), $request->getUri()->__toString()));
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}