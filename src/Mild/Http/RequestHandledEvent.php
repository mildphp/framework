<?php

namespace Mild\Http;

use Mild\Event\Event;
use Mild\Contract\Http\ResponseInterface;
use Mild\Contract\Http\ServerRequestInterface;

class RequestHandledEvent extends Event
{
    /**
     * @var ServerRequestInterface
     */
    public $request;
    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * RequestHandledEvent constructor.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}