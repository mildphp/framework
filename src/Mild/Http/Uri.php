<?php

namespace Mild\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * @var string
     */
    protected $host;
    /**
     * @var string
     */
    protected $port;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $query;
    /**
     * @var string
     */
    protected $scheme;
    /**
     * @var string
     */
    protected $fragment;
    /**
     * @var string
     */
    protected $userInfo;

    /**
     * Uri constructor.
     *
     * @param $scheme
     * @param $host
     * @param null $port
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @param string $user
     * @param string $password
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    )
    {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $this->filterHost($host);
        $this->port = $this->filterPort($port);
        $this->path = $this->filterPath($path);
        $this->query = $this->filterQueryOrFragment($query);
        $this->fragment = $this->filterQueryOrFragment($fragment);
        $this->userInfo = $this->filterUserInfo($user);
        if ($password !== '') {
            $this->userInfo .= ':'.$this->filterUserInfo($password);
        }
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo.'@'.$authority;
        }
        if ($this->port !== null) {
            $authority .= ':'.$this->port;
        }
        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
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
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     * @return static
     */
    public function withScheme($scheme)
    {
        $clone = clone $this;
        $clone->scheme = $this->filterScheme($scheme);
        $clone->port = $clone->filterPort($this->port);
        return $clone;
    }

    /**
     * @param string $user
     * @param null $password
     * @return static
     */
    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->userInfo = $this->filterUserInfo($user);
        if ($password !== null) {
            $clone->userInfo .= ':'.$this->filterUserInfo($password);
        }
        return $clone;
    }

    /**
     * @param string $host
     * @return static
     */
    public function withHost($host)
    {
        $clone = clone $this;
        $clone->host = $this->filterHost($host);
        return $clone;
    }

    /**
     * @param int|null $port
     * @return static
     */
    public function withPort($port)
    {
        $clone = clone $this;
        $clone->port = $this->filterPort($port);
        return $clone;
    }

    /**
     * @param string $path
     * @return static
     */
    public function withPath($path)
    {
        $clone = clone $this;
        $clone->path = $this->filterPath($path);
        return $clone;
    }

    /**
     * @param string $query
     * @return static
     */
    public function withQuery($query)
    {
        $clone = clone $this;
        $clone->query = $this->filterQueryOrFragment($query);
        return $clone;
    }

    /**
     * @param string $fragment
     * @return static
     */
    public function withFragment($fragment)
    {
        $clone = clone $this;
        $clone->fragment = $this->filterQueryOrFragment($fragment);
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $uri = '';
        if ($this->scheme !== '') {
            $uri .= $this->scheme.':';
        }
        if (($authority = $this->getAuthority()) !== '') {
            $uri .= '//'.$authority;
        }
        $uri .= $this->path;
        if ($this->query !== '') {
            $uri .= '?'.$this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#'.$this->fragment;
        }
        return $uri;
    }

    /**
     * @param $scheme
     * @return string
     */
    protected function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new InvalidArgumentException('The scheme or fragment must be an string.');
        }
        $scheme = strtolower($scheme);
        if (($index = strpos($scheme, ':')) !== false) {
            $scheme = substr($scheme, 0, $index);
        }
        return $scheme;
    }

    /**
     * @param $host
     * @return string
     */
    protected function filterHost($host)
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException('The host is invalid type.');
        }
        return strtolower($host);
    }

    /**
     * @param int|null $port
     * @return int|null
     */
    protected function filterPort($port)
    {
        if ($port === null || is_integer($port) && $port >= 1 && $port <= 65535) {
            if ($port === getservbyname($this->scheme, 'tcp')) {
                $port = null;
            }
            return $port;
        }
        throw new InvalidArgumentException('The port value must be an integer between 1 and 65535.');
    }

    /**
     * @param $path
     * @return string|string[]|null
     */
    protected function filterPath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException('The path must be an string.');
        }

        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeFilter'],
            '/'.trim($path, '/')
        );
    }

    /**
     * @param $string
     * @return string|string[]|null
     */
    protected function filterQueryOrFragment($string)
    {
        if (!is_string($string)) {
            throw new InvalidArgumentException('The query or fragment must be an string.');
        }
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawUrlEncodeFilter'],
            $string
        );
    }

    /**
     * @param $userInfo
     * @return string|string[]|null
     */
    protected function filterUserInfo($userInfo)
    {
        if (!is_string($userInfo)) {
            throw new InvalidArgumentException('The user info must be an string.');
        }
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'rawUrlEncodeFilter'],
            $userInfo
        );
    }

    /**
     * @param array $matches
     * @return string
     */
    private function rawUrlEncodeFilter(array $matches)
    {
        return rawurlencode($matches[0]);
    }
}