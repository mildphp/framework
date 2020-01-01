<?php

namespace Mild\Http;

use Exception;
use Mild\Contract\Http\HttpExceptionInterface;

class HttpException extends Exception implements HttpExceptionInterface
{
    /**
     * @var int
     */
    protected $statusCode;
    /**
     * @var string
     */
    protected $reasonPhrase;

    /**
     * HttpException constructor.
     *
     * @param int $statusCode
     * @param string $reasonPhrase
     */
    public function __construct($statusCode = 500, $reasonPhrase = '')
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
        parent::__construct('', 0, null);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }
}