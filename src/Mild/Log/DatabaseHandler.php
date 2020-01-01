<?php

namespace Mild\Log;

use Throwable;
use Psr\Log\LogLevel;
use Mild\Database\Connection;
use Mild\Support\Traits\DatabaseHandlerTrait;

class DatabaseHandler extends AbstractHandler
{
    use DatabaseHandlerTrait;

    const COL_TIME  = 'time';
    const COL_LEVEL = 'level';
    const COL_CONTEXT = 'context';
    const COL_CHANNEL = 'channel';
    const COL_MESSAGE   = 'message';

    /**
     * DatabaseHandler constructor.
     *
     * @param Connection $connection
     * @param $table
     * @param array $columns
     * @param string $minLevel
     */
    public function __construct(Connection $connection, $table, array $columns, $minLevel = LogLevel::DEBUG)
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->columns = $columns;

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
        $this->createQuery()->insert([
            $this->getColumn(self::COL_LEVEL)   => $level,
            $this->getColumn(self::COL_CHANNEL) => $channel,
            $this->getColumn(self::COL_MESSAGE) => $message,
            $this->getColumn(self::COL_TIME)    => date('Y-m-d h:i:s'),
            $this->getColumn(self::COL_CONTEXT) => $this->composeContext($context)
        ]);
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