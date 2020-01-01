<?php

namespace Mild\Contract\Validation;

use Throwable;

interface ValidationExceptionInterface extends Throwable
{
    /**
     * @return ValidatorInterface
     */
    public function getValidator();
}