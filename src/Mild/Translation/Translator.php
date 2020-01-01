<?php

namespace Mild\Translation;

use Mild\Contract\Translation\RepositoryInterface;
use Mild\Contract\Translation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    /**
     * @var string|null
     */
    protected $locale;
    /**
     * @var RepositoryInterface
     */
    protected $repository;
    /**
     * @var string|null
     */
    protected $fallbackLocale;

    /**
     * Translator constructor.
     *
     * @param RepositoryInterface $repository
     * @param null $locale
     * @param null $fallbackLocale
     */
    public function __construct(RepositoryInterface $repository, $locale = null, $fallbackLocale = null)
    {
        $this->repository = $repository;
        $this->setLocale($locale);
        $this->setFallbackLocale($fallbackLocale);
    }

    /**
     * @param $key
     * @param null $locale
     * @return bool
     */
    public function has($key, $locale = null)
    {
        return $this->get($key, [], $locale, false) !== $key;
    }

    /**
     * @param $key
     * @param array $replacements
     * @param null $locale
     * @param bool $fallback
     * @return mixed|string|string[]|null
     */
    public function get($key, array $replacements = [], $locale = null, $fallback = true)
    {
        if (strpos($key, '::') !== false) {
            $clone = clone $this;

            [$space, $key] = explode('::', $key, 2);

            $clone->repository = $this->repository->getSpace($space);

            return $clone->get($key, $replacements, $locale, $fallback);
        }

        if (($result = $this->repository->get(($locale ?: $this->locale).'.'.$key, $key)) === $key && true === $fallback && !empty($this->fallbackLocale)) {
            $result = $this->get($key, $replacements, $this->fallbackLocale, false);
        }

        if ($result !== $key) {
            foreach ($replacements as $key => $value) {
                $result = preg_replace('/{'.$key.'}/i', $value, $result);
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @param $value
     * @param null $locale
     * @return void
     */
    public function set($key, $value, $locale = null)
    {
        $this->repository->set(($locale ?: $this->locale).'.'.$key, $value);
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return string|null
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * @param $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string|null $fallbackLocale
     * @return void
     */
    public function setFallbackLocale($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }
}