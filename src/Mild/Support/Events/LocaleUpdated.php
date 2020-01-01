<?php

namespace Mild\Support\Events;

use Mild\Event\Event;

class LocaleUpdated extends Event
{
    /**
     * @var string
     */
    public $locale;

    /**
     * LocaleUpdated constructor.
     *
     * @param $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}