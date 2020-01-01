<?php

namespace Mild\Contract\Cookie;

interface FactoryInterface
{
    /**
     * @param $key
     * @return CookieInterface
     */
    public function get($key);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param CookieInterface $cookie
     * @return void
     */
    public function set(CookieInterface $cookie);

    /**
     * @param $key
     * @return void
     */
    public function put($key);

    /**
     * @return array
     */
    public function getCookies();

    /**
     * @param $name
     * @param string $value
     * @param int $expiration
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return CookieInterface
     */
    public function make($name, $value = '', $expiration = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null);

    /**
     * @param $name
     * @param string $value
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return CookieInterface
     */
    public function forever($name, $value = '', $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null);

    /**
     * @param $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return CookieInterface
     */
    public function forget($name, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null);
}