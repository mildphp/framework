<?php

namespace Mild\Support\Facades;

use Mild\Contract\Translation\RepositoryInterface;

/**
 * Class Translation
 *
 * @package \Mild\Support\Facades\Mild\Support\Facades
 * @see \Mild\Translation\Translator
 * @method static mixed|string|string[]|null get($key, $replacements, $locale = null, bool $fallback = true)
 * @method static void set($key, $value, $locale = null)
 * @method static string|null getLocale()
 * @method static RepositoryInterface getRepository()
 * @method static string|null getFallbackLocale()
 * @method static void setLocale($locale)
 * @method static void setFallbackLocale($fallbackLocale)
 */
class Translation extends Facade
{
    /**
     * @return object|string
     */
    protected static function getAccessor()
    {
        return 'translation';
    }
}