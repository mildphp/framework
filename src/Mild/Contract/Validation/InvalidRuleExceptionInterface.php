<?php

namespace Mild\Contract\Validation;

use Throwable;

interface InvalidRuleExceptionInterface extends Throwable
{
    /**
     * @return mixed
     */
    public function getRule();
}