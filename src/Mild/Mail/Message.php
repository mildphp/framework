<?php

namespace Mild\Mail;

use DateTime;
use Exception;
use Mild\Contract\Mail\MessageInterface;

class Message implements MessageInterface
{
    /**
     * @var BodyCollection
     */
    public $body;
    /**
     * @var HeaderCollection
     */
    public $header;
    /**
     * @var IdGenerator
     */
    public $generator;
    /**
     * @var string
     */
    protected $from;
    /**
     * @var array
     */
    protected $recipients = [];

    /**
     * Message constructor.
     *
     * @param IdGenerator $generator
     */
    public function __construct(IdGenerator $generator)
    {
        $this->generator = $generator;
        $this->body = new BodyCollection;
        $this->header = new HeaderCollection;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param $email
     * @return void
     */
    public function addRecipient($email)
    {
        $this->recipients[] = $email;
    }

    /**
     * @param $email
     * @return void
     */
    public function setFrom($email)
    {
        $this->from = $email;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toString()
    {
        $this->header->set('Message-ID', sprintf('<%s>', $this->generator->generate()));
        $this->header->set('Date', (new DateTime)->format('r'));
        $this->header->set('MIME-Version', '1.0');
        $this->header->set('X-Powered-By', 'Mild Mailer');

        return $this->header->toString()."\r\n".$this->body->toString();
    }
}