<?php

namespace Mild\Contract\Validation;

use Mild\Contract\Container\ContainerInterface;

interface FactoryInterface
{
    /**
     * @param array $data
     * @param array $rules
     * @return ValidatorInterface
     */
    public function make(array $data, array $rules);

    /**
     * @return array
     */
    public function getRules();

    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @param array $rules
     * @return void
     */
    public function setRules(array $rules);
}