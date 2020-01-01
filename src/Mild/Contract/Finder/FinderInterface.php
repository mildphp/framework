<?php

namespace Mild\Contract\Finder;

use IteratorAggregate;

interface FinderInterface extends IteratorAggregate
{

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return int
     */
    public function getDepth();

    /**
     * @return array
     */
    public function getFilters();

    /**
     * @param $depth
     * @return void
     */
    public function setDepth($depth);

    /**
     * @param array $filters
     * @return void
     */
    public function setFilters(array $filters);

    /**
     * @param FilterInterface $filter
     * @return void
     */
    public function addFilter(FilterInterface $filter);
}