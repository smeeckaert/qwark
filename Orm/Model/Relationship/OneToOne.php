<?php

namespace Qwark\Orm\Model\Relationship;

class OneToOne extends Standard
{
    public function load($dbName)
    {
        $model = $this->toModel;
        $data = [$this->toKey => $this->model->{$this->fromKey}];
        return $model::findOne($data, $dbName);
    }
}