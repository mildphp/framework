<?php

namespace Mild\Validation;

use Mild\Database\Connection;
use Mild\Database\Query\Builder;
use Mild\Contract\Validation\GatherDataInterface;
use Mild\Contract\Translation\TranslatorInterface;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class UniqueRule extends Rule
{
    /**
     * @var Builder
     */
    private $query;
    /**
     * @var string
     */
    private $column;

    /**
     * UniqueRule constructor.
     * @param Connection $connection
     * @param $table
     * @param null $column
     */
    public function __construct(Connection $connection, $table, $column = null)
    {
        $this->column = $column;
        $this->query = $connection->table($table);
    }

    /**
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return bool
     * @throws CompilerExceptionInterface
     */
    protected function passes(GatherDataInterface $data, $key, $value)
    {
        return $this->query->where($this->column ?: $key, '=', $value)->exists() === false;
    }

    /**
     * @param TranslatorInterface $translator
     * @param GatherDataInterface $data
     * @param $key
     * @param $value
     * @return string
     */
    protected function message(TranslatorInterface $translator, GatherDataInterface $data, $key, $value)
    {
        return $translator->get('validation.unique', [
            'attribute' => $key
        ]);
    }
}