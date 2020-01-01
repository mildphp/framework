<?php

namespace Mild\Translation;

use Mild\Contract\Translation\TranslatorInterface;

class UpdateTranslationLocale
{
    /**
     * @param $event
     * @param TranslatorInterface $translator
     */
    public function __invoke($event, TranslatorInterface $translator)
    {
        $translator->setLocale($event->locale);
    }
}