<?php

namespace Mild\Cache;

use DateTimeInterface;
use Mild\Finder\Finder;
use InvalidArgumentException;
use Mild\Contract\Cache\HandlerInterface;

class FileHandler implements HandlerInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * FileHandler constructor.
     *
     * @param $path
     */
    public function __construct($path)
    {
        if (!is_writable($path)) {
            throw new InvalidArgumentException(sprintf(
                'Directory %s is not writable', $path
            ));
        }

        $this->path = rtrim($path, '\/').DIRECTORY_SEPARATOR;
    }

    /**
     * @param $key
     * @return mixed|void
     */
    public function get($key)
    {
        $payload = $this->getPayload($key);

        if ($payload['expiration'] < time()) {
            $this->put($key);
            $payload['value'] = null;
        }

        return $payload['value'];
    }

    /**
     * @param $key
     * @param $value
     * @param mixed $expiration
     */
    public function set($key, $value, $expiration)
    {
        if ($expiration instanceof DateTimeInterface) {
            $expiration = $expiration->format('U');
        } elseif (!is_numeric($expiration) && ($expiration = strtotime($expiration)) === false) {
            throw new InvalidArgumentException(sprintf(
                'The expiration time is invalid type.'
            ));
        }

        // Set forever expiration
        if ($expiration == 0) {
            $expiration = INF;
        }

        if (!is_dir($path = dirname($file = $this->resolveFileName($key)))) {
            @mkdir($path, 0777, true);
        }

        @file_put_contents($file, serialize([
            'value' => $value,
            'expiration' => $expiration
        ]), LOCK_EX);
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        if (is_file($file = $this->resolveFileName($key))) {
            @unlink($file);
        }
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function increment($key, int $value = 1)
    {
        $payload = $this->getPayload($key);

        $payload['value'] = (int) $payload['value'] + $value;

        $this->set($key, $payload['value'], $payload['expiration']);

        return $payload['value'];
    }

    /**
     * @param $key
     * @param int $value
     * @return int
     */
    public function decrement($key, int $value = 1)
    {
        $payload = $this->getPayload($key);

        $payload['value'] = (int) $payload['value'] - $value;

        $this->set($key, $payload['value'], $payload['expiration']);

        return $payload['value'];
    }

    /**
     * @return void
     */
    public function flush()
    {
        foreach (Finder::instance($this->path, 1)->dirs() as $spl) {
            $this->deleteDirectory($spl);
        }
    }

    /**
     * @param $key
     * @return string
     */
    protected function resolveFileName($key)
    {
        $parts = array_slice(str_split($hash = sha1($key), 2), 0, 2);

        return $this->path.implode(DIRECTORY_SEPARATOR, $parts).DIRECTORY_SEPARATOR.$hash;
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    protected function getPayload($key)
    {
        if (!is_file($file = $this->resolveFileName($key)) || !is_array($payload = unserialize(file_get_contents($file))) || !array_key_exists('value', $payload) || !array_key_exists('expiration', $payload)) {
            return ['value' => null, 'expiration' => 0];
        }

        return $payload;
    }

    /**
     * @param $path
     * @return void
     */
    protected function deleteDirectory($path)
    {
        foreach (Finder::instance($path) as $spl) {
            if ($spl->isDir()) {
                $this->deleteDirectory($spl->getRealPath());
            } else {
                @unlink($spl->getPathname());
            }
        }
        @rmdir($path);
    }
}