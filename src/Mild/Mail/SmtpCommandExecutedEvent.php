<?php

namespace Mild\Mail;

use Mild\Event\Event;

class SmtpCommandExecutedEvent extends Event
{
    /**
     * @var string
     */
    public $command;
    /**
     * @var string
     */
    public $response;

    /**
     * SmtpCommandExecutedEvent constructor.
     *
     * @param $command
     * @param $response
     */
    public function __construct($command, $response)
    {
        $this->command = $command;
        $this->response = $response;
    }
}