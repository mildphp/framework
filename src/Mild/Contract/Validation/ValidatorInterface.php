<?php

namespace Mild\Contract\Validation;

interface ValidatorInterface
{
    /**
     * @return void
     * @throws ValidationExceptionInterface
     */
    public function validate();

    /**
     * @return GatherDataInterface
     */
    public function getData();

    /**
     * @return array
     */
    public function getRules();

    /**
     * @return MessageInterface
     */
    public function getMessage();
}