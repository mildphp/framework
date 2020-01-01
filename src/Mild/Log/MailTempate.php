<?php

namespace Mild\Log;

use Mild\Mail\SimpleMessage;

class MailTempate
{
    /**
     * @var MailHandler
     */
    private $handler;
    private $channel;
    private $level;
    private $message;
    private $context;

    /**
     * MailTempate constructor.
     *
     * @param MailHandler $handler
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     */
    public function __construct(MailHandler $handler, $channel, $level, $message, $context)
    {
        $this->handler = $handler;
        $this->channel = $channel;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @param SimpleMessage $message
     */
    public function __invoke($message)
    {
        call_user_func($this->handler->resolver, $message, $this->channel, $this->level, $this->message, $this->context);
    }
}