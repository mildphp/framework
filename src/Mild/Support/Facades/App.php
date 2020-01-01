<?php

namespace Mild\Support\Facades;

use Mild\Contract\ProviderInterface;
use Mild\Contract\BootstrapInterface;

/**
 * Class App
 *
 * @package Mild\Support\Facades
 * @see \Mild\Application
 * @method static string getName()
 * @method static array getProviders()
 * @method static void setName($name)
 * @method static string getLocale()
 * @method static void setLocale($locale)
 * @method static string getBasePath()
 * @method static void setBasePath($basePath)
 * @method static array getDeferredProviders()
 * @method static bool runningInConsole()
 * @method static void provider(ProviderInterface $provider)
 * @method static void boot()
 * @method static bool isBooted()
 * @method static void bootstrap(BootstrapInterface $bootstrap)
 * @method static mixed get($key)
 * @method static array all()
 * @method static bool has($key)
 * @method static void put($key)
 * @method static void set($key, $value)
 * @method static void bind($key, $value)
 * @method static void alias($key, $value)
 * @method static mixed make($abstract, $arguments)
 * @method static bool offsetExists($offset)
 * @method static mixed offsetGet($offset)
 * @method static void offsetSet($offset, $value)
 * @method static void offsetUnset($offset)
 */
class App extends Facade
{
    /**
     * @return string
     */
    protected static function getAccessor()
    {
        return 'app';
    }
}