<?php

namespace Qwark\Orm\Model\Relationship;

use Qwark\Orm\Model;
use Qwark\Orm\Tools;

class ManyToMany extends Standard
{
    protected $assoc;
    protected $assocFrom;
    protected $assocTo;
    protected $assocPrefix;

    /**
     * ManyToMany constructor.
     * @param $params
     * @param Model $model
     */
    public function __construct($params, $model)
    {
        parent::__construct($params, $model);
        $this->adaptTo();
        $this->adaptFrom();
        /**
         * Set default assoc table name
         */
        if (!is_string($this->params['assoc'])) {
            $this->assoc = Tools::assocTableName($this->from, $this->to);
        }
        if (empty($this->params['assocFrom'])) {
            $prefixFrom = call_user_func([$this->fromModel, 'prefix']);
            $this->assocFrom = "{$prefixFrom}_{$this->fromKey}";
        }

        if (empty($this->params['assocTo'])) {
            $prefixFrom = call_user_func([$this->toModel, 'prefix']);
            $this->assocTo = "{$prefixFrom}_{$this->toKey}";
        }

        if (empty($this->params['assocPrefix'])) {
            $this->assocPrefix = Tools::assocPrefix($this->from, $this->to);
        }
        $this->setParamsAndCheck(['assoc', 'assocFrom', 'assocTo', 'assocPrefix']);
    }

    public function load($dbName)
    {
        /** @var Model\GenericBuilder $builder */
        $builder = call_user_func([$this->toModel, 'builder']);
        $fromPrefix = call_user_func([$this->fromModel, 'prefix']);
        $builder->addPrefix($this->assoc, $this->assocPrefix);
        $builder->addPrefix($this->from, $fromPrefix);
        $query = $builder->select();
        $query->leftJoin($this->assoc, $this->toKey, $this->assocTo, ['*']);
        $query->leftJoin($this->from, "{$this->assoc}." . $this->assocFrom, $this->fromKey, ['*']);
        $query->where()->equals("{$this->from}." . $this->fromKey, $this->model->{$this->fromKey});
        $model = $this->toModel;
        return $model::find($query, $dbName);
    }
}