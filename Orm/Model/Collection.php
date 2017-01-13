<?php

namespace Qwark\Orm\Model;

use Qwark\Orm\Model;

class Collection implements \Iterator
{
    protected $position = 0;
    protected $data = [];
    protected $class;
    protected $dbName;

    /**
     * Collection constructor.
     * @param \PDOStatement $result
     */
    public function __construct($result, $class, $dbName)
    {
        $this->position = 0;
        $this->class = $class;
        $this->dbName = $dbName;

        while (($row = $result->fetch(\PDO::FETCH_ASSOC))) {
            $this->data[] = $row;
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return Model
     */
    function current()
    {
        if (!empty($this->data[$this->position])) {
            return new $this->class($this->data[$this->position], $this->dbName);
        }
        return null;
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->data[$this->position]);
    }
}