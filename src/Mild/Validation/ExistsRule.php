<?php

namespace Mild\Validation;

use Mild\Contract\Translation\TranslatorInterface;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class ExistsRule extends UniqueRule
{
    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     * @throws CompilerExceptionInterface
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return parent::passes($data, $key, $value) === false;
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
        return $translator->get('validation.exists', [
            'attribute' => $key
        ]);
    }
}