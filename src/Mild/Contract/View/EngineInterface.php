<?php

namespace Mild\Contract\View;

interface EngineInterface
{
    /**
     * @return string
     */
    public function getFile();

    /**
     * @return CompilerInterface
     */
    public function getCompiler();

    /**
     * @return array
     */
    public function getData();

    /**
     * @return array
     */
    public function getSections();

    /**
     * @param $key
     * @param null $default
     * @return string
     */
    public function getSection($key, $default = null);

    /**
     * @param array $sections
     * @return void
     */
    public function setSections(array $sections);

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setSection($key, $value);

    /**
     * @param array $data
     * @return void
     */
    public function setData(array $data);

    /**
     * @param $data
     * @return void
     */
    public function addData(array $data);

    /**
     * @return string
     */
    public function render();
}