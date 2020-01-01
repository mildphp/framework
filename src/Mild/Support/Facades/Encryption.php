<?php

namespace Mild\Support\Facades;

/**
 * Class Encryption
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Encryption\Encrypter
 * @method static string getKey()
 * @method static string getCipher()
 * @method static void setCipher($cipher)
 * @method static string encrypt($value)
 * @method static mixed|string decrypt($value)
 */
class Encryption extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'encrypter';
    }
}