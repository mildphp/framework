<?php

namespace Mild\Validation;

use InvalidArgumentException;
use Mild\Contract\Http\UploadedFileInterface;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;

class MimeTypesRule extends Rule
{
    /**
     * @var array
     */
    private $types;

    /**
     * MimeTypesRule constructor.
     */
    public function __construct()
    {
        if (count($types = func_get_args()) === 0) {
            throw new InvalidArgumentException('The argument missing types.');
        }

        $this->types = $types;
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

        return in_array($value->getClientMediaType(), $this->types);
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
        return $translator->get('validation.mimes', [
            'attribute' => $key,
            'type' => implode(', ', $this->types)
        ]);
    }
}