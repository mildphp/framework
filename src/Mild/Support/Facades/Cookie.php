<?php

namespace Mild\Support\Facades;

use Mild\Contract\Cookie\CookieInterface;

/**
 * Class Cookie
 *
 * @package \Mild\Support\Facades
 * @see \Mild\Cookie\Factory
 * @method static CookieInterface get($key)
 * @method static void set(CookieInterface $cookie)
 * @method static bool has($key)
 * @method static void put($key)
 * @method static array getCookies()
 * @method static Cookie make($name, string $value = '', int $expiration = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, $sameSite = null)
 * @method static Cookie forever($name, string $value = '', string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, $sameSite = null)
 * @method static Cookie forget($name, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, $sameSite = null)
 */
class Cookie extends Facade
{

    /**
     * @return string|object
     */
    protected static function getAccessor()
    {
        return 'cookie';
    }
}