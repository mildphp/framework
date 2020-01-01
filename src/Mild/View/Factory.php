<?php

namespace Mild\View;

use Mild\Contract\View\FactoryInterface;
use Mild\Contract\View\CompilerInterface;
use Mild\Contract\View\RepositoryInterface;
use Mild\Contract\Event\EventDispatcherInterface;

class Factory implements FactoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    public $event;
    /**
     * @var Variable
     */
    public $variable;
    /**
     * @var CompilerInterface
     */
    protected $compiler;
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Factory constructor.
     *
     * @param EventDispatcherInterface $event
     * @param RepositoryInterface $repository
     * @param CompilerInterface $compiler
     */
    public function __construct(EventDispatcherInterface $event, RepositoryInterface $repository, CompilerInterface $compiler)
    {
        $this->event = $event;
        $this->compiler = $compiler;
        $this->repository = $repository;
        $this->variable = new Variable([
            '__view' => $this,
            '__error' => new ErrorBag
        ]);
    }

    /**
     * @param $file
     * @param array $data
     * @param array $sections
     * @return Engine
     */
    public function make($file, $data = [], $sections = [])
    {
        $this->event->dispatch(new Event(
            $engine = new Engine($this->compiler, $this->repository->get($file), $this->variable->merge($data), $sections)
        ));

        return $engine;
    }

    /**
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }
}