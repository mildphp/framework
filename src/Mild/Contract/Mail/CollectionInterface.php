<?php

namespace Mild\Contract\Mail;

interface CollectionInterface
{
    /**
     * @return array
     */
    public function getItems();

    /**
     * @return string
     */
    public function toString();
}