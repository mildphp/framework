<?php

namespace Mild\Validation;

use Mild\Contract\Translation\TranslatorInterface;
use Mild\Contract\Validation\GatherDataInterface;

class RegexRule extends Rule
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * RegexRule constructor.
     *
     * @param $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return (bool) preg_match($this->pattern, $value);
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
        return $translator->get('validation.regex', [
            'attribute' => $key
        ]);
    }
}