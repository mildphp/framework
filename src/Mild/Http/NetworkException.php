<?php

namespace Mild\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Client\NetworkExceptionInterface;

class NetworkException extends ClientException implements NetworkExceptionInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * NetworkException constructor.
     *
     * @param RequestInterface $request
     * @param string $message
     */
    public function __construct(RequestInterface $request, $message = '')
    {
        $this->request = $request;
        parent::__construct($message);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}