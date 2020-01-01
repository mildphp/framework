<?php

namespace Mild\Validation;

use Mild\Support\MessageBag;
use Mild\Contract\Validation\MessageInterface;
use Mild\Contract\Translation\TranslatorInterface;

class Message extends MessageBag implements MessageInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Message constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $items
     */
    public function __construct(TranslatorInterface $translator, $items = [])
    {
        parent::__construct($items);

        $this->translator = $translator;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->add($key, $value);
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}