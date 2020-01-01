<?php

namespace Mild\Finder;

use SplFileInfo;
use DateTimeInterface;
use InvalidArgumentException;
use Mild\Contract\Finder\FilterInterface;

class DateFilter implements FilterInterface
{
    /**
     * @var mixed
     */
    private $time;
    /**
     * @var string
     */
    private $operator;

    /**
     * DateFilter constructor.
     *
     * @param $time
     * @param null $operator
     */
    public function __construct($time, $operator = null)
    {
        if (!is_numeric($time) && ($time = strtotime($time)) === false) {
            throw new InvalidArgumentException('The time is invalid type.');
        }
        if ($time instanceof DateTimeInterface) {
            $time = $time->format('U');
        }
        $this->time = $time;
        $this->operator = $operator;
    }

    /**
     * @param SplFileInfo $splFileInfo
     * @return bool
     */
    public function accept(SplFileInfo $splFileInfo)
    {
        $time = $splFileInfo->getMTime();
        switch ($this->operator) {
            case '<':
                return $time < $this->time;
                break;
            case '>':
                return $time > $this->time;
                break;
            case '<=':
                return $time <= $this->time;
                break;
            case '>=':
                return $time >= $this->time;
                break;
            case '!=':
                return $time != $this->time;
                break;
            default:
                return $time === $this->time;
                break;
        }
    }
}