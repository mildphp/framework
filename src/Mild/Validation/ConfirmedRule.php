<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class ConfirmedRule extends Rule
{
    /**
     * @var null|string
     */
    private $name;

    /**
     * ConfirmedRule constructor.
     *
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return $data->get($this->resolveName($key)) === $value;
    }

    /**
     * @param $key
     * @return string
     */
    protected function resolveName($key)
    {
        if ($this->name === null) {
            return $key.'_confirmation';
        }
        return $this->name;
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
        return $translator->get('validation.confirmed', [
            'attribute' => $key
        ]);
    }
}