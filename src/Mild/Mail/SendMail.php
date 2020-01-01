<?php

namespace Mild\Mail;

use Exception;
use Mild\Contract\Mail\MessageInterface;

class SendMail extends AbstractTransport
{

    /**
     * @param MessageInterface|Message $message
     * @return void
     * @throws Exception
     */
    public function send(MessageInterface $message)
    {
        mail(implode(', ', $message->getRecipients()), $message->header->line('Subject'), $message->body->toString(), str_replace("\r\n\r\n", "\n", $message->toString()));
    }
}