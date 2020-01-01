<?php

namespace Mild\Contract\Cookie;

use DateTimeInterface;

interface CookieInterface
{
    /**
     * @var string
     */
    const SAMESITE_NONE = 'none';
    /**
     * @var string
     */
    const SAMESITE_LAX = 'lax';
    /**
     * @var string
     */
    const SAMESITE_STRICT = 'strict';

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getValue();

    /**
     * @return int
     */
    public function getExpiration();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return bool
     */
    public function isSecure();

    /**
     * @return bool
     */
    public function isHttpOnly();

    /**
     * @return string
     */
    public function getSameSite();

    /**
     * @param $value
     * @return void
     */
    public function setValue($value);

    /**
     * @param DateTimeInterface|int|string| $expiration
     * @return void
     */
    public function setExpiration($expiration);

    /**
     * @param $path
     * @return void
     */
    public function setPath($path);

    /**
     * @param $domain
     * @return void
     */
    public function setDomain($domain);

    /**
     * @param bool $secure
     * @return void
     */
    public function setSecure($secure);

    /**
     * @param bool $httpOnly
     * @return void
     */
    public function setHttpOnly($httpOnly);

    /**
     * @param $sameSite
     * @return mixed
     */
    public function setSameSite($sameSite);

    /**
     * @return string
     */
    public function __toString();
}