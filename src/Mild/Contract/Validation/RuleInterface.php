<?php

namespace Mild\Contract\Validation;

interface RuleInterface
{
    /**
     * @param MessageInterface $message
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return void
     */
    public function handle(MessageInterface $message, GatherDataInterface $data, $key, $value);
}