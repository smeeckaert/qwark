<?php

namespace Qwark\Orm\Model\Relationship;

class OneToMany extends Standard
{
    public function load($dbName)
    {
        $model = $this->toModel;
        $data = [$this->toKey => $this->model->{$this->fromKey}];
        return $model::find($data, $dbName);
    }
}