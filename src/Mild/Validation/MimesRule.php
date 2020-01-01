<?php

namespace Mild\Validation;

use InvalidArgumentException;
use Mild\Contract\Http\UploadedFileInterface;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class MimesRule extends Rule
{
    /**
     * @var array
     */
    private $extensions = [];

    /**
     * MimesRule constructor.
     */
    public function __construct()
    {
        if (count($extensions = func_get_args()) === 0) {
            throw new InvalidArgumentException('The argument missing extensions.');
        }

        $this->extensions = $extensions;
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param UploadedFileInterface $value
     * @return bool
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        if (!$this->isUploadedFile($value)) {
            return false;
        }

        return in_array($value->getExtension(), $this->extensions);
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
        return $translator->get('validation.mimes', [
            'attribute' => $key,
            'type' => implode(', ', $this->extensions)
        ]);
    }
}