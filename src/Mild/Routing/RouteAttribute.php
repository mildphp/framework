<?php

namespace Mild\Routing;

use BadMethodCallException;
use Mild\Contract\Routing\RouteAttributeInterface;

abstract class RouteAttribute implements RouteAttributeInterface
{
    /**
     * @var array
     */
    protected $attributes = [];
    /**
     * Attribute yang di izinkan.
     *
     * @var array
     */
    protected $allowAttributes = [];
    /**
     * Attribute yang di aliaskan
     *
     * @var array
     */
    protected $aliasAttributes = [];
    /**
     * Menyatukan value attribute yang baru dengan attribute yang lama.
     *
     * @var array
     */
    protected $mergeValueAttributes = [];
    /**
     * Nilai default dari attribute.
     *
     * @var array
     */
    protected $defaultValueAttributes = [];

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->$key($value);
        }
    }

    /**
     * @param $name
     * @param array $arguments
     * @return $this|mixed|null
     */
    public function __call($name, array $arguments = [])
    {
        // Jika anda mendefinisikan alias attribute maka kita akan mengganti
        // key dengan key attribute yang asli
        if (isset($this->aliasAttributes[$name])) {
            $name = $this->aliasAttributes[$name];
        }

        // Jika attribute tidak di izinkan maka kita akan melempar error.
        if (!isset($this->allowAttributes[$name])) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exists.', static::class, $name
            ));
        }

        // Jika tidak terdapat indeks ke nol [0] dari arguments, maka kita
        // mendeteksi bahwa anda melakukan pemanggilan nilai dari nama metode di attribute.
        if (!isset($arguments[0])) {
            return $this->attributes[$name] ?? ($this->defaultValueAttributes[$name] ?? null);
        }

        // Jika anda mendefinisikan attribute tertentu harus di satukan nilai yang
        // lama dengan nilai yang baru, maka kita harus menyatukan nilai yang baru
        // dengan nilai yang lama
        if (isset($this->mergeValueAttributes[$name])) {
            $arguments[0] = $this->{$this->mergeValueAttributes[$name]}($arguments[0]);
        }

        $this->attributes[$name] = $arguments[0];

        return $this;
    }
}