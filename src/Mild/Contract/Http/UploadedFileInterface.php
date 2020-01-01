<?php

namespace Mild\Contract\Http;

use Psr\Http\Message\UploadedFileInterface as PsrUploadedFileInterface;

interface UploadedFileInterface extends PsrUploadedFileInterface
{
    /**
     * @return bool
     */
    public function isMoved();
    /**
     * @return string
     */
    public function getExtension();
}