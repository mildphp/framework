<?php

namespace Mild\Contract\Hashing;

interface HasherInterface
{
    /**
     * @param $hash
     * @return mixed
     */
    public static function info($hash);

    /**
     * @param $value
     * @param $hash
     * @return bool
     */
    public static function check($value, $hash);

    /**
     * @param $hash
     * @param array $options
     * @return bool
     */
    public static function rehash($hash, array $options = []);

    /**
     * @param $value
     * @param array $options
     * @return string
     */
    public static function hash($value, array $options = []);
}