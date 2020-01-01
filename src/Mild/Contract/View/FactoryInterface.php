<?php

namespace Mild\Contract\View;

interface FactoryInterface
{
    /**
     * @return RepositoryInterface
     */
    public function getRepository();

    /**
     * @return CompilerInterface
     */
    public function getCompiler();

    /**
     * @param $file
     * @param array $data
     * @param array $sections
     * @return mixed
     */
    public function make($file, $data = [], $sections = []);
}