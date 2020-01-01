<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class RequiredRule extends Rule
{

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return !empty($value);
    }

    /**
     * @param TranslatorInterface $translator
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return mixed|string
     */
    protected function message(TranslatorInterface $translator, GatherDataInterface $data, $key, $value)
    {
        return $translator->get('validation.required', ['attribute' => $key]);
    }
}