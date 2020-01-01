<?php

namespace Mild\Mail;

use Mild\Event\Event;

class SmtpDisconnectedEvent extends Event
{
    /**
     * @var string
     */
    public $response;

    /**
     * SmtpDisconnectedEvent constructor.
     *
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
    }
}