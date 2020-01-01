<?php

namespace Mild\Validation;

use Mild\Contract\Validation\RuleInterface;
use Mild\Contract\Validation\MessageInterface;
use Mild\Contract\Validation\ShouldSkipInterface;
use Mild\Contract\Validation\GatherDataInterface;

abstract class ShouldSkip implements RuleInterface, ShouldSkipInterface
{
    /**
     * @param MessageInterface $message
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     */
    public function handle(MessageInterface $message, GatherDataInterface $data, $key, $value)
    {
        //
    }
}