<?php

namespace Mild\Log;

use Throwable;
use Psr\Log\LogLevel;
use Mild\Http\Factory;
use Mild\Contract\Http\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;

class SlackWebhookHandler extends AbstractHandler
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $icon;
    /**
     * @var string
     */
    protected $channel;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * SlackWebhookHandler constructor.
     *
     * @param ClientInterface $client
     * @param $url
     * @param null $channel
     * @param null $username
     * @param null $icon
     * @param string $minLevel
     */
    public function __construct(ClientInterface $client, $url, $channel = null, $username = null, $icon = null, $minLevel = LogLevel::DEBUG)
    {
        $this->client = $client;
        $this->url = $url;

        $this->setIcon($icon);
        $this->setChannel($channel);
        $this->setUsername($username);

        parent::__construct($minLevel);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $channel
     * @return void
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * @param $username
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param $icon
     * @return void
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     * @return void
     * @throws ClientExceptionInterface
     */
    protected function writeLog($channel, $level, $message, $context)
    {
        if ($this->channel) {
            $data['channel'] = $this->channel;
        }

        $data['username'] = $this->username ?: $channel;

        switch ($level) {
            case LogLevel::ERROR:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
                $color = 'danger';
                break;
            case LogLevel::WARNING:
                $color = 'warning';
                break;
            default:
                $color = '#0000CD';
                break;
        }

        foreach ($context as $key => $value) {
            if ($value === null || is_scalar($value)) {
                $context[$key] = $value;
            } elseif (is_object($value)) {
                $class = get_class($value);
                if ($value instanceof Throwable) {
                    $context[$key] = sprintf("[object] (%s (code: %s)) %s at %s:%s\n[stacktrace]\n%s", $class, $value->getCode(), $value->getMessage(), $value->getFile(), $value->getLine(), $value->getTraceAsString());
                } elseif (method_exists($value, '__toString')) {
                    $context[$key] = sprintf('[object] (%s) %s', $class, $value->__toString());
                } else {
                    $context[$key] = sprintf('[object] (%s: %s)', $class, $this->toJson($value));
                }
            } elseif (is_resource($value)) {
                $context[$key] = sprintf('[resource] (%s)', get_resource_type($value));
            } else {
                $context[$key] = sprintf('[unknown] (%s)', gettype($value));
            }
        }

        $fields = [
            [
                'title' => 'Level',
                'value' => strtoupper($level)
            ]
        ];

        if ($context) {
            $fields[] = [
                'title' => 'Context',
                'value' => sprintf('```%s```', str_replace(['\r', '\n'], ["\r", "\n"], $this->toJson($context)))
            ];
        }

        $data['attachments'] = [
            [
                'fallback' => $message,
                'color' => $color,
                'title' => 'Message',
                'text' => $message,
                'fields' => $fields,
                'mrkdwn_in' => ['fields']
            ]
        ];

        if ($this->icon) {
            $data['icon_'.((((bool) preg_match('/:(.*):/s', $this->icon)) ? 'emoji' : 'url'))] = $this->icon;
        }

        $request = Factory::createRequest('POST', $this->url);

        $request->getBody()->write(json_encode($data));

        $this->client->sendRequest($request->withHeader('Content-Type', 'application/json'));
    }

    /**
     * @param $value
     * @return string
     */
    private function toJson($value)
    {
        return json_encode($value, JSON_PRETTY_PRINT,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}