<?php

namespace Mild\Hashing;

use RuntimeException;
use Mild\Contract\Hashing\HasherInterface;

abstract class AbstractHasher implements HasherInterface
{
    /**
     * @param $hash
     * @return array
     */
    public static function info($hash)
    {
        return tap(password_get_info($hash), function ($info) {
            if ($info['algoName'] === 'unknown') {
                self::throwRuntimeException('The hash value is invalid algorithm.');
            }
        });
    }

    /**
     * @param $value
     * @param $hash
     * @return bool
     */
    public static function check($value, $hash)
    {
        if (self::info($hash)['algo'] !== static::getAlgorithm()) {
            self::throwRuntimeException('The algorithm is not match.');
        }

        return password_verify($value, $hash);
    }

    /**
     * @param $message
     * @return void
     */
    protected static function throwRuntimeException($message)
    {
        throw new RuntimeException($message);
    }

    /**
     * @return int
     */
    abstract protected static function getAlgorithm();
}