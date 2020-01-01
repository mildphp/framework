<?php

namespace Mild\Validation;

use Exception;
use Mild\Contract\Validation\ValidatorInterface;
use Mild\Contract\Validation\ValidationExceptionInterface;

class ValidationException extends Exception implements ValidationExceptionInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * ValidationException constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        parent::__construct($validator->getMessage());
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return $this->validator;
    }
}