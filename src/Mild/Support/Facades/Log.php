<?php

namespace Mild\Support\Facades;

use Mild\Contract\Log\HandlerInterface;

/**
 * Class Log
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Log\Logger
 * @method static void addHandler(HandlerInterface $handler)
 * @method static void emergency($message, $context)
 * @method static void alert($message, $context)
 * @method static void critical($message, $context)
 * @method static void error($message, $context)
 * @method static void warning($message, $context)
 * @method static void notice($message, $context)
 * @method static void info($message, $context)
 * @method static void debug($message, $context)
 * @method static void log($level, $message, $context)
 * @method static string getChannel()
 * @method static array getHandlers()
 * @method static void setChannel($channel)
 * @method static void setHandlers($handlers)
 */
class Log extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'log';
    }
}