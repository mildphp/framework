<?php

namespace Mild\Contract\Pipeline;

use Mild\Contract\Container\ContainerInterface;

interface PipelineInterface
{
    /**
     * @param callable $destination
     * @param array $arguments
     * @return mixed
     */
    public function send(callable $destination, $arguments = []);

    /**
     * @return array
     */
    public function getPipes();

    /**
     * @return ContainerInterface
     */
    public function getContainer();

    /**
     * @param $pipe
     * @return void
     */
    public function addPipe($pipe);

    /**
     * @param array $pipes
     * @return void
     */
    public function setPipes(array $pipes);
}