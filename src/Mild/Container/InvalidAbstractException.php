<?php

namespace Mild\Container;

use InvalidArgumentException;
use Mild\Contract\Container\InvalidAbstractExceptionInterface;

class InvalidAbstractException extends InvalidArgumentException implements InvalidAbstractExceptionInterface
{
    /**
     * @var mixed
     */
    protected $abstract;

    /**
     * InvalidAbstractException constructor.
     *
     * @param $abstract
     */
    public function __construct($abstract)
    {
        $this->abstract = $abstract;

        parent::__construct('Abstract is not provided.');
    }

    /**
     * @return mixed
     */
    public function getAbstract()
    {
        return $this->abstract;
    }
}