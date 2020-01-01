<?php

namespace Mild\View;

use Mild\Support\Arr;
use Mild\Support\Dot;
use Mild\Support\MessageBag;

class ErrorBag extends Dot
{
    /**
     * ViewErrorBag constructor.
     *
     * @param array $items
     */
    public function __construct($items = [])
    {
        $this->setItems($items);
    }

    /**
     * @param $key
     * @param null $default
     * @return MessageBag
     */
    public function get($key, $default = null)
    {
        return $this->toMessageBag(parent::get($key, $default));
    }

    /**
     * Jika value yang di hasilkan oleh metode get() itu bukan instansi dari MessageBag,
     * Maka kita akan mengkonvert value tersebut menjadi MessageBag.
     *
     * @param $value
     * @return MessageBag
     */
    protected function toMessageBag($value)
    {
        if ($value instanceof MessageBag) {
            return $value;
        }

        return new MessageBag(Arr::wrap($value));
    }
}