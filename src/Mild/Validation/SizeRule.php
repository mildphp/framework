<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class SizeRule extends Rule
{
    /**
     * @var int
     */
    private $length;

    /**
     * SizeRule constructor.
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
        return $this->getSize($value) == $this->length;
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

        return $translator->get('validation.size.'.$key, [
            'attribute' => $key,
            'length' => $this->length
        ]);
    }
}