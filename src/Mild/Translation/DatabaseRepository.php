<?php

namespace Mild\Translation;

use Mild\Database\Connection;
use Mild\Support\Traits\DatabaseHandlerTrait;
use Mild\Contract\Database\Exceptions\CompilerExceptionInterface;

class DatabaseRepository extends Repository
{
    use DatabaseHandlerTrait;

    const COL_KEY = 'key';
    const COL_VALUE = 'value';
    const COL_LOCALE = 'locale';

    /**
     * DatabaseRepository constructor.
     *
     * @param Connection $connection
     * @param $table
     * @param array $columns
     * @param array $spaces
     */
    public function __construct(Connection $connection, $table, array $columns, $spaces = [])
    {
        parent::__construct($spaces);

        $this->table = $table;
        $this->columns = $columns;
        $this->connection = $connection;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     * @throws CompilerExceptionInterface
     */
    public function get($key, $default = null)
    {
        if (!$this->has($key)) {
            [$locale, $name] = explode('.', $key, 2);
            $selected = $this->getColumn(self::COL_VALUE);
            if ($value = $this->createQuery()
                ->select($selected)
                ->where($this->getColumn(self::COL_LOCALE),  '=', $locale)
                ->where($this->getColumn(self::COL_KEY), '=', $name)
                ->first()) {
                $this->set($key, $value->{$selected});
            }
        }

        return parent::get($key, $default);
    }
}