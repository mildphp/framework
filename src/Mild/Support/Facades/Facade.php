<?php

namespace Mild\Support\Facades;

use RuntimeException;
use Mild\Contract\ApplicationInterface;

abstract class Facade
{
    /**
     * @var ApplicationInterface|null
     */
    protected static $application;
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @param ApplicationInterface $application
     */
    public static function setApplication(ApplicationInterface $application)
    {
        static::$application = $application;
    }

    /**
     * @return ApplicationInterface|null
     */
    public static function getApplication()
    {
        return static::$application;
    }

    /**
     * @param null $key
     * @return void
     */
    public static function clear($key = null)
    {
        if (null !== $key) {
            unset(self::$instances[$key]);
        } else {
            self::$instances = [];
        }
    }

    /**
     * @return mixed|string
     */
    public static function instance()
    {
        if (static::$application === null) {
            throw new RuntimeException('The application on facade is not set.');
        }

        if (is_object($instance = static::getAccessor())) {
            return $instance;
        }

        if (isset(self::$instances[$instance])) {
            return self::$instances[$instance];
        }

        return self::$instances[$instance] = static::$application->make($instance);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, array $arguments = [])
    {
        return static::instance()->$name(...$arguments);
    }

    /**
     * @return string|object
     */
    abstract protected static function getAccessor();
}