<?php

namespace Mild\Database\Entity\Relations;

use Mild\Support\Collection;

class BelongsTo extends Relation
{
    /**
     * @param Collection|null $collection
     * @return mixed
     */
    public function execute(Collection $collection = null)
    {
        if (null === $collection) {
            return $this->builder->__call('where', [$this->primaryKey, '=', $this->model->{$this->foreignKey}])->first();
        }

        if (!empty($keys = $this->getKeys($collection))) {
            /**
             * @var Collection $results
             */
            $results = $this->builder->__call('whereIn', [$this->primaryKey, $keys])->get();

            foreach ($collection as $model) {
                $model->setRelation($this->name, $results->filter(function ($children) use ($model) {
                    return $children->{$this->primaryKey} === $model->{$this->foreignKey};
                })->first());
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
        return  $collection->map(function ($model) {
            return $model->{$this->foreignKey};
        })->all();
    }
}