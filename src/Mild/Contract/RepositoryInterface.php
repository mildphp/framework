<?php

namespace Mild\Contract;

interface RepositoryInterface
{
    /**
     * @return array
     */
    public function getItems();

    /**
     * @param array $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @param array $items
     * @return void
     */
    public function mergeItems(array $items);

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null);

    /**
     * @param $key
     * @return bool
     */
    public function has($key);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function add($key, $value);

    /**
     * @param $key
     * @return void
     */
    public function put($key);

    /**
     * Alias metode dari getItems().
     *
     * @return array
     */
    public function all();
}