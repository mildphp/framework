<?php

namespace Mild\Mail;

use Mild\Support\Str;

class IdGenerator
{
    /**
     * @var string
     */
    public $host;

    /**
     * IdGenerator constructor.
     *
     * @param $host
     */
    public function __construct($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return Str::random(32).'@'.$this->host;
    }
}