<?php

namespace Mild\Log;

use Psr\Log\LogLevel;
use InvalidArgumentException;
use Mild\Contract\Log\HandlerInterface;

abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $minLevel;

    /**
     * @var array
     */
    private static $levels = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7
    ];

    /**
     * AbstractHandler constructor.
     *
     * @param string $minLevel
     */
    public function __construct($minLevel = LogLevel::DEBUG)
    {
        $this->setMinLevel($minLevel);
    }

    /**
     * @param $level
     * @return void
     */
    public function setMinLevel($level)
    {
        $this->assertValidLevel($level);

        $this->minLevel = $level;
    }

    /**
     * @return string
     */
    public function getMinLevel()
    {
        return $this->minLevel;
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param array $context
     * @return void
     */
    public function handle($channel, $level, $message, array $context = [])
    {
        $this->assertValidLevel($level);

        if (self::$levels[$level] >= self::$levels[$this->minLevel]) {
            $this->writeLog($channel, $level, $message, $context);
        }
    }

    /**
     * @param $level
     * @return void
     */
    protected function assertValidLevel($level)
    {
        if (!isset(self::$levels[$level])) {
            throw new InvalidArgumentException('Level is not defined.');
        }
    }

    /**
     * @param $channel
     * @param $level
     * @param $message
     * @param $context
     * @return void
     */
    abstract protected function writeLog($channel, $level, $message, $context);
}