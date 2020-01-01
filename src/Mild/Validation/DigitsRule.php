<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class DigitsRule extends Rule
{
    /**
     * @var int
     */
    private $length;

    /**
     * DigitsRule constructor.
     *
     * @param $length
     */
    public function __construct($length)
    {
        $this->length = $length;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return !preg_match('/[^0-9]/', $value) && strlen((string) $value) == $this->length;
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
        return $translator->get('validation.digits', [
            'attribute' => $key,
            'length' => $this->length
        ]);
    }
}