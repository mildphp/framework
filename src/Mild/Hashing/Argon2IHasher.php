<?php

namespace Mild\Hashing;

class Argon2IHasher extends AbstractHasher
{
    const DEFAULT_THREADS       = 2;
    const DEFAULT_TIME_COST     = 2;
    const DEFAULT_MEMORY_COST   = 1024;
    const ALGORITHM             = PASSWORD_ARGON2I;

    /**
     * @return int
     */
    protected static function getAlgorithm()
    {
        return static::ALGORITHM;
    }

    /**
     * @param $hash
     * @param array $options
     * @return bool
     */
    public static function rehash($hash, array $options = [])
    {
        $info = parent::info($hash);

        if ($info['algo'] !== static::ALGORITHM) {
            parent::throwRuntimeException('The algorithm is not match.');
        }

        $options += $info['options'];

        return password_needs_rehash($hash, static::ALGORITHM, [
            'threads'     => $options['threads'] ?? static::DEFAULT_THREADS,
            'time_cost'   => $options['time_cost'] ?? static::DEFAULT_TIME_COST,
            'memory_cost' => $options['memory_cost'] ?? static::DEFAULT_MEMORY_COST
        ]);
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public static function hash($value, array $options = [])
    {
        return tap(password_hash($value, static::ALGORITHM, [
            'threads'     => $options['threads'] ?? static::DEFAULT_THREADS,
            'time_cost'   => $options['time_cost'] ?? static::DEFAULT_TIME_COST,
            'memory_cost' => $options['memory_cost'] ?? static::DEFAULT_MEMORY_COST
        ]), function ($hash) {
            if (false === $hash) {
                parent::throwRuntimeException('Cannot hashing value.');
            }
        });
    }
}