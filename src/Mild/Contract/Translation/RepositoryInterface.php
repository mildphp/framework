<?php

namespace Mild\Contract\Translation;

use Mild\Contract\RepositoryInterface as BaseRepositoryInterface;

interface RepositoryInterface extends BaseRepositoryInterface
{
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