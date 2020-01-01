<?php

namespace Mild\Contract\Database\Query;

use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

interface CompilerInterface
{
    /**
     * @return array
     */
    public function getOperators();

    /**
     * @param $value
     * @return string
     */
    public function wrap($value);

    /**
     * @return string
     */
    public function getTablePrefix();

    /**
     * @param $operator
     * @return bool
     */
    public function isValidOperator($operator);

    /**
     * @param $prefix
     * @return void
     */
    public function setTablePrefix($prefix);

    /**
     * @param BuilderInterface $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function compileExists(BuilderInterface $builder);

    /**
     * @param BuilderInterface $builder
     * @return string
     * @throws CompilerExceptionInterface
     */
    public function compileSelect(BuilderInterface $builder);

    /**
     * @param BuilderInterface $builder
     * @return string
     */
    public function compileDelete(BuilderInterface $builder);

    /**
     * @param BuilderInterface $builder
     * @param array $values
     * @return string
     */
    public function compileInsert(BuilderInterface $builder, array $values);

    /**
     * @param BuilderInterface $builder
     * @param array $values
     * @return string
     */
    public function compileInsertOrIgnore(BuilderInterface $builder, array $values);

    /**
     * @param BuilderInterface $builder
     * @param array $values
     * @return string
     */
    public function compileUpdate(BuilderInterface $builder, array $values);
}