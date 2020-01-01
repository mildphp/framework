<?php

namespace Mild\Validation;

use DateTime;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class DateFormatRule extends Rule
{
    /**
     * @var string
     */
    private $format;

    /**
     * DateFormatRule constructor.
     *
     * @param $format
     */
    public function __construct($format)
    {
        $this->format = $format;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        $date = DateTime::createFromFormat('!'.$this->format, $value);

        return $date && $date->format($this->format) === $value;
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
        return $translator->get('validation.date_format', [
            'attribute' => $key,
            'format' => $this->format
        ]);
    }
}