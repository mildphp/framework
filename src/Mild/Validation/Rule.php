<?php

namespace Mild\Validation;

use Countable;
use Mild\Support\Str;
use Mild\Contract\Validation\RuleInterface;
use Mild\Contract\Http\UploadedFileInterface;
use Mild\Contract\Validation\MessageInterface;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

abstract class Rule implements RuleInterface
{
    /**
     * @var string
     */
    protected $customMessage;

    /**
     * @param MessageInterface $message
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return void
     */
    public function handle(MessageInterface $message, GatherDataInterface $data, $key, $value)
    {
        if ($this->passes($data, $key, $value) === false) {
            $message->add($key, ($this->customMessage === null) ? $this->message($message->getTranslator(), $data, $key, $value) : $this->customMessage);
        }
    }

    /**
     * @return string
     */
    public function getCustomMessage()
    {
        return $this->customMessage;
    }

    /**
     * @param $message
     */
    public function setCustomMessage($message)
    {
        $this->customMessage = $message;
    }

    /**
     * @param $value
     * @return int
     */
    protected function getSize($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        if (is_array($value) || $value instanceof Countable) {
            return count($value);
        }

        if ($this->isUploadedFile($value)) {
            /**
             * @var UploadedFileInterface $value
             */
            return $value->getSize() / 1024;
        }

        return Str::length($value);
    }

    /**
     * @param $first
     * @param $second
     * @return bool
     */
    protected function isSameType($first, $second)
    {
        return gettype($first) === gettype($second);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isUploadedFile($value)
    {
        return $value instanceof UploadedFileInterface;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     */
    abstract protected function passes(GatherDataInterface $data, $key, $value);

    /**
     * @param TranslatorInterface $translator
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return string
     */
    abstract protected function message(TranslatorInterface $translator, GatherDataInterface $data, $key, $value);
}