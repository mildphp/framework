<?php

namespace Mild\Contract\Validation;

use Mild\Contract\RepositoryInterface;
use Mild\Contract\Translation\TranslatorInterface;

interface MessageInterface extends RepositoryInterface
{
    /**
     * @param $key
     * @return mixed|null
     */
    public function first($key);

    /**
     * @param $key
     * @return mixed|null
     */
    public function last($key);

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * @return bool
     */
    public function isNotEmpty();

    /**
     * @return TranslatorInterface
     */
    public function getTranslator();
}