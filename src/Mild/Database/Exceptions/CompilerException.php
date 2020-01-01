<?php

namespace Mild\Database\Exceptions;

use Exception;
use Mild\Contract\Database\Query\CompilerInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class CompilerException extends Exception implements CompilerExceptionInterface
{
    /**
     * @var CompilerInterface
     */
    protected $compiler;

    /**
     * CompilerException constructor.
     *
     * @param CompilerInterface $compiler
     * @param string $message
     */
    public function __construct(CompilerInterface $compiler, $message = "")
    {
        $this->compiler = $compiler;

        parent::__construct($message.' ('.get_class($compiler).')');
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}