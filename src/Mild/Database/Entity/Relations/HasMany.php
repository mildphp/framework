<?php

namespace Mild\Database\Entity\Relations;

use Mild\Support\Collection;

class HasMany extends Relation
{
    /**
     * @param Collection|null $collection
     * @return Collection|mixed
     */
    public function execute(Collection $collection= null)
    {
        if ($collection === null) {
            return $this->builder->__call('where', [$this->foreignKey, '=', $this->model->{$this->primaryKey}])->get();
        }

        if (!empty($keys = $this->getKeys($collection))) {
            /**
             * @var Collection $results
             */
            $results = $this->builder->__call('whereIn', [$this->foreignKey, $keys])->get();

            foreach ($collection as $model) {
                $model->setRelation($this->name, $results->filter(function ($item) use ($model) {
                    return $model->{$this->primaryKey} === $item->{$this->foreignKey};
                }));
            }
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return array
     */
    protected function getKeys($collection)
    {
        return $collection->map(function ($model) {
            return $model->{$this->primaryKey};
        })->all();
    }
}