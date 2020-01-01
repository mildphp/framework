<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class DigitsBetween extends Rule
{
    /**
     * @var int
     */
    private $min;
    /**
     * @var int
     */
    private $max;

    /**
     * DigitsBetween constructor.
     *
     * @param $min
     * @param $max
     */
    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        $length = strlen((string) $value);

        return !preg_match('/[^0-9]/', $value)
            && $length >= $this->min && $length <= $this->max;
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
        return $translator->get('validation.digits_between', [
            'min' => $this->min,
            'max' => $this->max,
            'attribute' => $key
        ]);
    }
}