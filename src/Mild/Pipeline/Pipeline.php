<?php

namespace Mild\Pipeline;

use Closure;
use Mild\Support\Arr;
use Mild\Contract\Pipeline\PipelineInterface;
use Mild\Contract\Container\ContainerInterface;

class Pipeline implements PipelineInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var array
     */
    protected $pipes = [];

    /**
     * Pipeline constructor.
     *
     * @param ContainerInterface $container
     * @param array $pipes
     */
    public function __construct(ContainerInterface $container, $pipes = [])
    {
        $this->container = $container;

        $this->setPipes($pipes);
    }

    /**
     * @return $this
     */
    public function pipe()
    {
        foreach (func_get_args() as $arg) {
            $this->addPipe($arg);
        }

        return $this;
    }

    /**
     * @param callable $destination
     * @param array $arguments
     * @return mixed
     */
    public function send(callable $destination, $arguments = [])
    {
        return array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->initial($destination)
        )(...Arr::wrap($arguments));
    }

    /**
     * @return array
     */
    public function getPipes()
    {
        return $this->pipes;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $pipe
     * @return void
     */
    public function addPipe($pipe)
    {
        $this->pipes[] = $pipe;
    }

    /**
     * @param array $pipes
     * @return void
     */
    public function setPipes(array $pipes)
    {
        $this->pipes = $pipes;
    }

    /**
     * @param callable $destination
     * @return Closure
     */
    protected function initial(callable $destination)
    {
        return function () use ($destination) {
            return $destination(...func_get_args());
        };
    }

    /**
     * @return Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function (...$args) use ($stack, $pipe) {
                $args[] = $stack;

                if (!is_callable($pipe)) {
                    $pipe = $this->container->make($pipe);
                }

                return $this->container->make($pipe, $args);
            };
        };
    }
}