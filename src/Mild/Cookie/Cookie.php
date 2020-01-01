<?php

namespace Mild\Cookie;

use DateTimeInterface;
use InvalidArgumentException;
use Mild\Contract\Cookie\CookieInterface;

class Cookie implements CookieInterface
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $value;
    /**
     * @var int
     */
    protected $expiration;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var bool
     */
    protected $secure;
    /**
     * @var bool
     */
    protected $httpOnly;
    /**
     * @var string
     */
    protected $sameSite;

    /**
     * Cookie constructor.
     *
     * @param $name
     * @param string $value
     * @param int $expiration
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string $sameSite
     */
    public function __construct(
        $name,
        $value = '',
        $expiration = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = true,
        $sameSite = null
    )
    {
        // Nama dari cookie harus tipe data string, dan tidak boleh ada
        // ilegal karaketer.
        if (!is_string($name) ||
            $name === '' ||
            preg_match('/[=,; \t\r\n\013\014]/', $name)
        ) {
            throw new InvalidArgumentException(sprintf(
                'The name should be an valid string type and should be not empty.'
            ));
        }
        $this->name = $name;
        $this->setValue($value);
        $this->setExpiration($expiration);
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($httpOnly);
        $this->setSameSite($sameSite);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure === true;
    }

    /**
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly === true;
    }

    /**
     * @return string
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * @param $value
     * @return void
     */
    public function setValue($value)
    {
        if ($value === null) {
            $value = '';
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value must be an string type'
            ));
        }
        $this->value = $value;
    }

    /**
     * @param DateTimeInterface|int|string $expiration
     * @return void
     */
    public function setExpiration($expiration)
    {
        if ($expiration instanceof DateTimeInterface) {
            $expiration = $expiration->format('U');
        } elseif (!is_numeric($expiration) && ($expiration = strtotime($expiration)) === false) {
            throw new InvalidArgumentException(sprintf(
                'The expiration time is invalid type.'
            ));
        }
        $this->expiration = $expiration > 0 ? (int) $expiration : 0;
    }

    /**
     * @param $path
     * @return void
     */
    public function setPath($path)
    {
        if ($path === '' || $path === null) {
            $path = '/';
        }
        if (!is_string($path)) {
            throw new InvalidArgumentException(sprintf(
                'The path must be an string type.'
            ));
        }
        $this->path = preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeFilter'],
            $path
        );
    }

    /**
     * @param $domain
     * @return void
     */
    public function setDomain($domain)
    {
        if ($domain === null) {
            $domain = '';
        }
        if (!is_string($domain)) {
            throw new InvalidArgumentException(sprintf(
                'The domain must be an string type.'
            ));
        }
        $this->domain = $domain;
    }

    /**
     * @param bool $secure
     * @return void
     */
    public function setSecure($secure)
    {
        if (is_bool($secure) === false) {
            throw new InvalidArgumentException(sprintf(
                'The secure must be an boolean type.'
            ));
        }
        $this->secure = $secure;
    }

    /**
     * @param bool $httpOnly
     * @return void
     */
    public function setHttpOnly($httpOnly)
    {
        if (is_bool($httpOnly) === false) {
            throw new InvalidArgumentException(sprintf(
                'The httpOnly must be an boolean type.'
            ));
        }
        $this->httpOnly = $httpOnly;
    }

    /**
     * @param $sameSite
     * @return void
     */
    public function setSameSite($sameSite)
    {
        if ($sameSite === '') {
            $sameSite = null;
        }
        if ($sameSite !== null &&
            ($sameSite = strtolower($sameSite)) !== self::SAMESITE_LAX &&
            $sameSite !== self::SAMESITE_NONE &&
            $sameSite !== self::SAMESITE_STRICT
        ) {
            throw new InvalidArgumentException(sprintf(
                'The same site is invalid.'
            ));
        }
        $this->sameSite = $sameSite;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $format = urlencode($this->name).'=';
        if ($this->value === '') {
            $format .= 'deleted; Expires='.gmdate('D, d-M-Y H:i:s T', time() - 31536001).'; Max-Age=0';
        } else {
            $format .= rawurlencode($this->value);
            if ($this->expiration !== 0) {
                $format .= '; Expires='.gmdate('D, d-M-Y H:i:s T', $this->expiration).'; Max-Age='.($this->expiration - time());
            }
        }
        if ($this->path !== '') {
            $format .= '; Path='.$this->path;
        }
        if ($this->domain !== '') {
            $format .= '; Domain='.$this->domain;
        }
        if ($this->secure) {
            $format .= '; Secure';
        }
        if ($this->httpOnly) {
            $format .= '; HttpOnly';
        }
        if ($this->sameSite !== null) {
            $format .= '; SameSite='.$this->sameSite;
        }
        return $format;
    }

    /**
     * @param array $matches
     * @return string
     */
    private function rawUrlEncodeFilter($matches)
    {
        return rawurlencode($matches[0]);
    }
}