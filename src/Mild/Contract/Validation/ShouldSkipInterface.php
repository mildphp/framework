<?php

namespace Mild\Contract\Validation;

interface ShouldSkipInterface
{
    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return mixed
     */
    public function skipWhen(GatherDataInterface $data, $key, $value);
}