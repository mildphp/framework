<?php

namespace Mild\Mail;

use Mild\Event\Event;

class SmtpConnectedEvent extends Event
{
    /**
     * @var mixed
     */
    public $time;
    /**
     * @var string
     */
    public $host;
    /**
     * @var int
     */
    public $port;
    /**
     * @var int
     */
    public $timeout;
    /**
     * @var string
     */
    public $response;

    /**
     * SmtpConnectedEvent constructor.
     *
     * @param $time
     * @param $response
     * @param $host
     * @param $port
     * @param $timeout
     */
    public function __construct($time, $response, $host, $port, $timeout)
    {
        $this->time = $time;
        $this->response = $response;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }
}