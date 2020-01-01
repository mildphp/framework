<?php

namespace Mild\Contract\Translation;

interface TranslatorInterface
{
    /**
     * @param $key
     * @param null $locale
     * @return bool
     */
    public function has($key, $locale = null);

    /**
     * @param $key
     * @param array $replacements
     * @param null $locale
     * @param bool $fallback
     * @return mixed
     */
    public function get($key, array $replacements = [], $locale = null, $fallback = true);

    /**
     * @param $key
     * @param $value
     * @param null $locale
     * @return void
     */
    public function set($key, $value, $locale = null);

    /**
     * @return string|null
     */
    public function getLocale();

    /**
     * @return RepositoryInterface
     */
    public function getRepository();

    /**
     * @return string|null
     */
    public function getFallbackLocale();
    /**
     * @param $locale
     * @return void
     */
    public function setLocale($locale);

    /**
     * @param $locale
     * @return void
     */
    public function setFallbackLocale($locale);
}