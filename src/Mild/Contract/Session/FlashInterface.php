<?php

namespace Mild\Contract\Session;

use Mild\Contract\RepositoryInterface;

interface FlashInterface extends RepositoryInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param $name
     * @return void
     */
    public function setName($name);

    /**
     * @return ManagerInterface
     */
    public function getManager();
}