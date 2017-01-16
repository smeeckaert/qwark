<?php

namespace Qwark\Orm\Model\Relationship;

use NilPortugues\Sql\QueryBuilder\Manipulation\Select;
use Qwark\Orm\Model;

abstract class Standard implements IFace
{
    protected $params;
    /** @var Model */
    protected $model;
    protected $from;
    protected $fromKey;
    /** @var  Model */
    protected $fromModel;
    protected $to;
    protected $toKey;
    /** @var  Model */
    protected $toModel;

    /**
     * Standard constructor.
     * @param $params
     * @param Model $model
     */
    public function __construct($params, $model)
    {
        $invert = false;
        $this->model = $model;
        if (empty($params['from'])) {
            $params['from'] = get_class($model);
        }
        // If to is not set but we have a from, we invert them so the prefix goes the right way
        if (empty($params['to']) && !empty($params['from'])) {
            $from = $params['from'];
            $params['from'] = get_class($model);
            $params['to'] = $from;
            $this->params = $params;
            $this->adaptFrom();
            $invert = true;
        }
        $this->params = $params;

        $this->adaptTo($invert ? call_user_func_array([$this->fromModel, 'propToDb'], [$this->fromKey]) : null);
        if (!$invert) {
            $this->adaptFrom(call_user_func_array([$this->toModel, 'propToDb'], [$this->toKey]));
        }
    }

    protected function adaptFrom($default = null)
    {
        $this->adapt('from', $default);
    }

    protected function adaptTo($default = null)
    {
        $this->adapt('to', $default);
    }

    protected function adapt($type, $defaultKey = null)
    {
        if (class_exists($this->params[$type])) {
            $this->{"{$type}Model"} = $this->params[$type];
            $this->{"{$type}Key"} = !empty($defaultKey) ? $defaultKey : call_user_func([$this->{"{$type}Model"}, 'idKey']);
            $this->$type = call_user_func([$this->{"{$type}Model"}, 'table']);
        }
        $listCheck = ["{$type}Model", "{$type}Key", $type];
        $this->setParamsAndCheck($listCheck);
    }

    protected function setParamsAndCheck($listCheck)
    {
        foreach ($listCheck as $check) {
            if (empty($this->$check)) {
                if (!empty($this->params[$check])) {
                    $this->$check = $this->params[$check];
                } else {
                    // @todo do something nice for once
                    throw new \Exception("TODO complete $check");
                }
            }
        }
    }

    protected function defaultQuery($query)
    {

    }

    /**
     * @param Select $query
     */
    protected function applyQuery($query)
    {
        return $this->defaultQuery($query);
    }

    public function load($dbName)
    {
        // TODO: Implement load() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

}