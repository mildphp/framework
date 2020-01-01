<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class ImageRule extends MimesRule
{
    /**
     * ImageRule constructor.
     */
    public function __construct()
    {
        parent::__construct('jpeg', 'png', 'gif', 'bmp', 'svg');
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
        return $translator->get('validation.image', [
            'attribute' => $key
        ]);
    }
}