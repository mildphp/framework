<?php

namespace Mild\Contract\Session;

use SessionHandlerInterface;
use Mild\Contract\RepositoryInterface;

interface ManagerInterface extends RepositoryInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getPrefix();

    /**
     * @return SessionHandlerInterface
     */
    public function getHandler();

    /**
     * @param $id
     * @return void
     */
    public function setId($id);

    /**
     * @param $prefix
     * @return void
     */
    public function setPrefix($prefix);

    /**
     * @return bool
     */
    public function isStarted();

    /**
     * @return void
     */
    public function start();

    /**
     * @param bool $destroy
     * @return void
     */
    public function regenerate($destroy = false);

    /**
     * @return void
     */
    public function save();
}