<?php

namespace Mild\Contract\Translation;

interface FactoryInterface
{
    /**
     * @param array $config
     * @param null $locale
     * @return TranslatorInterface
     */
    public static function make(array $config, $locale = null);
}