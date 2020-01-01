<?php

namespace Mild\Contract\Mail;

interface BodyInterface
{
    /**
     * @return CollectionInterface
     */
    public function getHeader();

    /**
     * @return string
     */
    public function toString();
}