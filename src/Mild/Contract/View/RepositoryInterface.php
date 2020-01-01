<?php

namespace Mild\Contract\View;

use Mild\Contract\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return string
     */
    public function getPath();

    /**
     * @param $key
     * @return RepositoryInterface
     */
    public function getSpace($key);

    /**
     * @param $key
     * @return bool
     */
    public function hasSpace($key);

    /**
     * @param $key
     * @param RepositoryInterface $value
     * @return void
     */
    public function setSpace($key, RepositoryInterface $value);

    /**
     * @return array
     */
    public function getSpaces();

    /**
     * @param array $spaces
     * @return void
     */
    public function setSpaces(array $spaces);
}