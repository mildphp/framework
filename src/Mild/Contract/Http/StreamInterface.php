<?php

namespace Mild\Contract\Http;

use Psr\Http\Message\StreamInterface as PsrStreamInterface;

interface StreamInterface extends PsrStreamInterface
{
    /**
     * @param StreamInterface $stream
     * @return void
     */
    public function copy(StreamInterface $stream);
}