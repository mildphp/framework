<?php

namespace Mild\Mail;

use InvalidArgumentException;
use Mild\Contract\Mail\BodyInterface;
use Mild\Contract\View\EngineInterface;

class SimpleMessage
{
    /**
     * @var Message
     */
    public $message;

    /**
     * SimpleMessage constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param $subject
     * @return SimpleMessage
     */
    public function subject($subject)
    {
        return $this->header('Subject', $subject);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     */
    public function to($email, $name = '')
    {
        $this->message->addRecipient($email);

        return $this->headerEmailName('To', $email, $name);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     */
    public function cc($email, $name = '')
    {
        $this->message->addRecipient($email);

        return $this->headerEmailName('Cc', $email, $name);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     */
    public function bcc($email, $name = '')
    {
        $this->message->addRecipient($email);

        return $this->headerEmailName('Bcc', $email, $name);
    }

    /**
     * @param $email
     * @param $name
     * @return SimpleMessage
     */
    public function replyTo($email, $name = '')
    {
        return $this->headerEmailName('Reply-To', $email, $name);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     */
    public function inReplyTo($email, $name = '')
    {
        return $this->headerEmailName('In-Reply-To', $email, $name);
    }

    /**
     * @param $email
     * @return SimpleMessage
     */
    public function returnPath($email)
    {
        return $this->headerEmailName('Return-Path', $email, '', false);
    }

    /**
     * @param $email
     * @param string $name
     * @return SimpleMessage
     */
    public function from($email, $name = '')
    {
        $this->message->setFrom($email);

        return $this->headerEmailName('From', $email, $name, false);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $add
     * @return $this
     */
    public function header($key, $value, $add = true)
    {
        $this->message->header->{($add ? 'add' : 'set')}($key, $value);

        return $this;
    }

    /**
     * @param $body
     * @param callable|null $callable
     * @return $this
     */
    public function body($body, $callable = null)
    {
        if ($body instanceof BodyInterface === false) {
            $body = $this->resolveBody($body, $callable);
        }

        $this->message->body->add($body);

        return $this;
    }

    /**
     * @param $file
     * @param null $name
     * @return $this
     */
    public function attach($file, $name = null)
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(sprintf(
                'File %s does not exists', $file
            ));
        }

        if (null === $name) {
            $name = basename($file);
        }

        $body = $this->createBody(file_get_contents($file));
        $header = $body->getHeader();
        $header->set('Content-Type', $header->parameterize([
            mime_content_type($file),
            'name' => $name
        ]));
        $header->set('Content-Transfer-Encoding', Encoder::BASE64);
        $header->set('Content-Disposition', $header->parameterize([
            'attachment',
            'filename' => $name
        ]));

        return $this->body($body);
    }

    /**
     * @param $level
     * @return $this
     */
    public function priority($level)
    {
        switch ($level) {
            case 1:
                $value = sprintf('%s (Highest)', $level);
                break;
            case 2:
                $value = sprintf('%s (High)', $level);
                break;
            case 3:
                $value = sprintf('%s (Normal)', $level);
                break;
            case 4:
                $value = sprintf('%s (Low)', $level);
                break;
            default:
                $value = '5 (Lowest)';
                break;
        }

        $this->header('X-Priority', $value, false);
        return $this;
    }

    /**
     * @param $key
     * @param $email
     * @param $name
     * @param bool $add
     * @return SimpleMessage
     */
    protected function headerEmailName($key, $email, $name, $add = true)
    {
        if ($name) {
            $name .= ' ';
        }
        return $this->header($key, sprintf('%s<%s>', $name, $email), $add);
    }

    /**
     * @param $body
     * @param callable|null $callable
     * @return Body
     */
    protected function resolveBody($body, callable $callable = null)
    {
        if (($isView = $body instanceof EngineInterface)) {
            /**
             * @var EngineInterface $body
             */
            $body = $body->render();
        }
        $body = $this->createBody($body);
        $header = $body->getHeader();
        if (null !== $callable) {
            $callable($header);
        } else {
            $header->set('Content-Type', $header->parameterize([
                ($isView ? 'text/html' : 'text/plain'),
                'charset' => 'utf-8'
            ]));
            $header->set('Content-Transfer-Encoding', Encoder::QUOTED_PRINTABLE);
        }

        return $body;
    }

    /**
     * @param $contents
     * @return Body
     */
    protected function createBody($contents)
    {
        return new Body($contents);
    }
}