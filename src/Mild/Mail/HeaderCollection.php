<?php

namespace Mild\Mail;

use Mild\Support\Arr;
use InvalidArgumentException;
use Mild\Contract\Mail\CollectionInterface;

class HeaderCollection implements CollectionInterface
{
    /**
     * @var array
     */
    protected $items = [];
    /**
     * @var array
     */
    private $names = [];

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function parameterize(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_string($key)) {
                $parameters[$key] = sprintf('%s=%s', $key, $value);
            }
        }

        return implode('; ', $parameters);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return isset($this->names[$this->normalizeHeaderName($key)]);
    }

    /**
     * @param $key
     * @return array
     */
    public function get($key)
    {
        if (!isset($this->names[$key = $this->normalizeHeaderName($key)])) {
            return [];
        }

        return $this->items[$this->names[$key]];
    }

    /**
     * @param $key
     * @return string
     */
    public function line($key)
    {
        return implode(', ', $this->get($key));
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        if (isset($this->names[$normalized = $this->normalizeHeaderName($key)])) {
            $key = $this->names[$normalized];
        } else {
            $this->names[$normalized] = $key;
        }

        $this->items[$key] = Arr::wrap($value);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function add($key, $value)
    {
        $this->assertWhiteSpace($key);

        $this->set($key, array_merge($this->get($key), Arr::wrap($value)));
    }

    /**
     * @param $key
     * @return void
     */
    public function put($key)
    {
        if (isset($this->names[$normalized = $this->normalizeHeaderName($key)])) {
            $key = $this->names[$normalized];
        }

        unset($this->items[$key], $this->names[$normalized]);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return implode("\r\n", array_map([$this, 'mapHeader'], $this->names));
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function normalizeHeaderName($key)
    {
        return str_replace('_', '-', strtolower($key));
    }

    /**
     * @param $value
     * @return void
     */
    protected function assertWhiteSpace($value)
    {
        if (strpos($value, ' ') !== false) {
            throw new InvalidArgumentException(sprintf(
                'The [%s] cannot contains the whitespace.', $value
            ));
        }
    }

    /**
     * @param $name
     * @return string
     */
    private function mapHeader($name)
    {
        return sprintf('%s: %s', $name, $this->line($name));
    }
}