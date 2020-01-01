<?php

namespace Mild\Cookie;

use InvalidArgumentException;
use Mild\Contract\Cookie\CookieInterface;
use Mild\Contract\Cookie\FactoryInterface;

class Factory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $cookies = [];

    /**
     * @param $key
     * @return CookieInterface
     * @throws InvalidArgumentException
     */
    public function get($key)
    {
        if ($this->has($key) === false) {
            throw new InvalidArgumentException(sprintf(
                'Cookie %s does not exists.', $key
            ));
        }

        return $this->cookies[$key];
    }

    /**
     * @param CookieInterface $cookie
     * @return void
     */
    public function set(CookieInterface $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->cookies[$key]);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        unset($this->cookies[$key]);
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Membuat objek dari cookie dan masukan cookie ke dalam antrian untuk di convert
     * ke dalam response header.
     *
     * @param $name
     * @param string $value
     * @param int $expiration
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return Cookie
     */
    public function make($name, $value = '', $expiration = 0, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null)
    {
        return new Cookie($name, $value, $expiration, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * @param $name
     * @param string $value
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return Cookie
     */
    public function forever($name, $value = '', $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null)
    {
        return $this->make($name, $value, 2628000, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * @param $name
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param null $sameSite
     * @return Cookie
     */
    public function forget($name, $path = '/', $domain = '', $secure = false, $httpOnly = true, $sameSite = null)
    {
        return $this->make($name, '', -2628000, $path, $domain, $secure, $httpOnly, $sameSite);
    }
}