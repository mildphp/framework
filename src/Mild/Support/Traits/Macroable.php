<?php

namespace Mild\Support\Traits;

use Closure;
use BadMethodCallException;

trait Macroable
{
    /**
     * @var array
     */
    protected static $macros = [];

    /**
     * @param $name
     * @param Closure $closure
     * @return void
     */
    public static function macro($name, Closure $closure)
    {
        static::$macros[$name] = $closure;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function hasMacro($name)
    {
        return isset(static::$macros[$name]);
    }

    /**
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments = [])
    {
        return static::bindToClosure($name, $this)(...$arguments);
    }

    /**
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($name, array $arguments = [])
    {
        return static::bindToClosure($name)(...$arguments);
    }

    /**
     * @param $name
     * @param null $newThis
     * @return Closure
     */
    protected static function bindToClosure($name, $newThis = null)
    {
        if (!static::hasMacro($name)) {
            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exists', static::class, $name
            ));
        }

        return Closure::bind(static::$macros[$name], $newThis, static::class);
    }
}