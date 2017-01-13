<?php

namespace Qwark\Orm\Model;

use Qwark\Orm\Model;

class Decorator
{
    /** @var  Model */
    protected $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    public function __get($name)
    {
        // Check if it is a relation, if so, load it before accessing it.
        if ($this->instance->hasRelationship($name)) {
            return $this->instance->rel($name);
        }
        return $this->instance->$name;
    }

    public function __set($name, $value)
    {
        $this->instance->$name = $value;
    }

    public function __isset($name)
    {
        return isset($this->instance->$name);
    }

    public function __unset($name)
    {
        unset($this->instance->$name);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->instance, $name], $arguments);
    }

    public function __debugInfo()
    {
        return [$this->instance];
    }
}