<?php

namespace Qwark\Orm\Model\Relationship;

use NilPortugues\Sql\QueryBuilder\Manipulation\Select;
use Qwark\Orm\Model\GenericBuilder;

class OneToMany extends Standard
{
    public function load($dbName)
    {
        $model = $this->toModel;
        /** @var GenericBuilder $builder */
        $builder = call_user_func([$this->toModel, 'builder']);
        $query = $builder->select();
        $this->applyQuery($query);
        return $model::find($query, $dbName);
    }

    /**
     * @param Select $query
     */
    protected function defaultQuery($query)
    {
        $query->where()->equals($this->toKey, $this->model->{$this->fromKey});
    }
}