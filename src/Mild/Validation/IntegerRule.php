<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class IntegerRule extends Rule
{

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * @param TranslatorInterface $translator
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return string
     */
    protected function message(TranslatorInterface $translator, GatherDataInterface $data, $key, $value)
    {
        return $translator->get('validation.integer', [
            'attribute' => $key
        ]);
    }
}