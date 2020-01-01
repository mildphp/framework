<?php

namespace Mild\Mail;

use Mild\Event\Event;
use Mild\Contract\Mail\TransportInterface;

class MessageSendEvent extends Event
{
    /**
     * @var mixed
     */
    public $time;
    /**
     * @var Message
     */
    public $message;
    /**
     * @var TransportInterface
     */
    public $transport;

    /**
     * MessageSendEvent constructor.
     *
     * @param Message $message
     * @param TransportInterface $transport
     * @param $time
     */
    public function __construct(Message $message, TransportInterface $transport, $time)
    {
        $this->message = $message;
        $this->transport = $transport;
        $this->time = $time;
    }
}