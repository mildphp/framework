<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class BetweenRule extends Rule
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
     * BetweenRule constructor.
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
        $size = $this->getSize($value);

        return $size >= $this->min && $size <= $this->max;
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
        if (is_numeric($value)) {
            $key = 'numeric';
        } elseif (is_array($value)) {
            return 'The %s must have between %s and %s items.';
        } elseif ($this->isUploadedFile($value)) {
            $key = 'file';
        } else {
            $key = 'string';
        }

        return $translator->get('validation.between.'.$key, [
            'min' => $this->min,
            'max' => $this->max,
            'attribute' => $key
        ]);
    }
}