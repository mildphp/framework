<?php

namespace Mild\Validation;

use Mild\Contract\Validation\GatherDataInterface;

class Nullable extends ShouldSkip
{

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return mixed
     */
    public function skipWhen(GatherDataInterface $data, $key, $value)
    {
        return empty($value);
    }
}