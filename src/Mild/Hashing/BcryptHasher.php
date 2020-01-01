<?php

namespace Mild\Hashing;

class BcryptHasher extends AbstractHasher
{
    const DEFAULT_COST  = 10;
    const ALGORITHM     = PASSWORD_BCRYPT;

    /**
     * @param $hash
     * @param array $options
     * @return bool
     */
    public static function rehash($hash, array $options = [])
    {
        $info = parent::info($hash);

        if ($info['algo'] !== self::ALGORITHM) {
            parent::throwRuntimeException('The algorithm is not match.');
        }

        $options += $info['options'];

        return password_needs_rehash($hash, self::ALGORITHM, [
            'cost' => $options['cost'] ?? self::DEFAULT_COST
        ]);
    }

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public static function hash($value, array $options = [])
    {
        if (($cost = $options['cost'] ?? self::DEFAULT_COST) <= 3) {
            parent::throwRuntimeException('The cost should be more than 3.');
        }

        return tap(password_hash($value, self::ALGORITHM, [
            'cost' => $cost
        ]), function ($hash) {
            if (false === $hash) {
                parent::throwRuntimeException('Cannot hashing value.');
            }
        });
    }

    /**
     * @return int
     */
    protected static function getAlgorithm()
    {
        return self::ALGORITHM;
    }
}