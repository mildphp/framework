<?php

namespace Mild\Validation;

use InvalidArgumentException;
use Mild\Contract\Validation\InvalidRuleExceptionInterface;

class InvalidRuleException extends InvalidArgumentException implements InvalidRuleExceptionInterface
{
    /**
     * @var mixed
     */
    protected $rule;

    /**
     * UnexpectedRuleException constructor.
     *
     * @param $rule
     */
    public function __construct($rule)
    {
        parent::__construct((sprintf(
            'The aliased rule must be an string type, %s given.', getType($rule)
        )));

        $this->rule = $rule;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->rule;
    }
}