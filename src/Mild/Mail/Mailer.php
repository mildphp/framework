<?php

namespace Mild\Mail;

use Mild\Contract\Mail\MailerInterface;
use Mild\Contract\Mail\TransportInterface;
use Mild\Contract\Event\EventDispatcherInterface;

class Mailer implements MailerInterface
{
    /**
     * @var IdGenerator
     */
    public $generator;
    /**
     * @var TransportInterface
     */
    protected $transport;
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Mailer constructor.
     *
     * @param IdGenerator $generator
     * @param TransportInterface $transport
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(IdGenerator $generator, TransportInterface $transport, EventDispatcherInterface $eventDispatcher)
    {
        $this->generator = $generator;
        $this->transport = $transport;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param callable $callable
     * @return void
     */
    public function send(callable $callable)
    {
        $callable(new SimpleMessage($message = new Message($this->generator)));

        $time = microtime(true);

        $this->transport->send($message);

        $this->eventDispatcher->dispatch(new MessageSendEvent($message, $this->transport, elapsed_time($time)));
    }

    /**
     * @return TransportInterface
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}