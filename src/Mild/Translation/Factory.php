<?php

namespace Mild\Translation;

use Mild\Support\Arr;
use Mild\Support\Str;
use InvalidArgumentException;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Translation\FactoryInterface;
use Mild\Contract\Translation\RepositoryInterface;

class Factory implements FactoryInterface
{
    use Macroable;

    /**
     * @param array $config
     * @param null $locale
     * @return Translator
     */
    public static function make(array $config, $locale = null)
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException('Configuration missing driver.');
        }

        if (!isset($config['drivers'][$config['driver']])) {
            throw new InvalidArgumentException(sprintf(
                'Driver [%s] is not configured.', $config['driver']
            ));
        }

        return new Translator(self::createRepository($config['driver'], array_values(Arr::wrap($config['drivers'][$config['driver']]))), $locale, $config['fallback_locale'] ?? null);
    }

    /**
     * @param $path
     * @return FileRepository
     */
    public static function createFileRepository($path)
    {
        return new FileRepository($path);
    }

    /**
     * @param $driver
     * @param $config
     * @return RepositoryInterface
     */
    protected static function createRepository($driver, $config)
    {
        if (method_exists(self::class, $method = sprintf('create%sRepository', Str::studly($driver)))) {
            return self::$method(...$config);
        }

        if (self::hasMacro($driver)) {
            return self::$driver(...$config);
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot create [%s] repository', $driver
        ));
    }
}