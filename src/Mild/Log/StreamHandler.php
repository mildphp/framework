<?php

namespace Mild\Log;

use Throwable;
use Psr\Log\LogLevel;
use Mild\Http\Stream;

class StreamHandler extends AbstractHandler
{
    /**
     * @var string
     */
    private $stream;

    /**
     * StreamHandler constructor.
     *
     * @param $path
     * @param string $minLevel
     */
    public function __construct($path, $minLevel = LogLevel::DEBUG)
    {
        $this->stream = new Stream(fopen($path, 'a'));

        parent::__construct($minLevel);
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
        $this->stream->write(
            sprintf('[%s] %s.%s: %s %s', date('Y-m-d h:i:s'), $channel, $level, $message, $this->composeContext($context))."\n"
        );
    }

    /**
     * @param array $context
     * @return string
     */
    private function composeContext($context)
    {
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

        if ($context) {
            return str_replace(['\r', '\n'], ["\r", "\n"], $this->toJson($context));
        }

        return '';
    }

    /**
     * @param $value
     * @return string
     */
    private function toJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
    }
}