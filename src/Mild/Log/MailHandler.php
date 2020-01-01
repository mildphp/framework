<?php

namespace Mild\Log;

use Psr\Log\LogLevel;
use Mild\Contract\Mail\MailerInterface;

class MailHandler extends AbstractHandler
{
    /**
     * @var callable
     */
    public $resolver;
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * MailHandler constructor.
     *
     * @param MailerInterface $mailer
     * @param callable $resolver
     * @param string $minLevel
     */
    public function __construct(MailerInterface $mailer, callable $resolver, $minLevel = LogLevel::ERROR)
    {
        parent::__construct($minLevel);

        $this->mailer = $mailer;
        $this->resolver = $resolver;
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     * @return void
     */
    protected function writeLog($channel, $level, $message, $context)
    {
        $this->mailer->send(new MailTempate($this, $channel, $level, $message, json_encode($context)));
    }
}