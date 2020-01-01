<?php

namespace Mild\Validation;

use Mild\Contract\Validation\RuleInterface;
use Mild\Contract\Validation\MessageInterface;
use Mild\Contract\Container\ContainerInterface;
use Mild\Contract\Validation\GatherDataInterface;

class CallableRule implements RuleInterface
{
    /**
     * @var callable
     */
    private $callable;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var null
     */
    private $defaultMessage;

    /**
     * CallableRule constructor.
     *
     * @param ContainerInterface $container
     * @param callable $callable
     * @param null $defaultMessage
     */
    public function __construct(ContainerInterface $container, callable $callable, $defaultMessage = null)
    {
        $this->container = $container;
        $this->callable = $callable;
        $this->defaultMessage = $defaultMessage;
    }

    /**
     * @param MessageInterface $message
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return void
     */
    public function handle(MessageInterface $message, GatherDataInterface $data, $key, $value)
    {
        $this->container->make($this->callable, [$message, $data, $key, $value, $this->defaultMessage]);
    }
}