<?php

namespace Qwark\Orm\Model;

use NilPortugues\Sql\QueryBuilder\Manipulation\QueryInterface;
use Qwark\Tools\Arr;
use Qwark\Tools\Str;

class GenericBuilder extends \NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder
{
    protected $class;
    protected $table;
    protected $prefixes = [];

    public function __construct($prefix, $class, $table)
    {
        parent::__construct();
        $this->class = $class;
        $this->table = $table;
        $this->addPrefix($table, $prefix);
    }

    public function rel($relName)
    {
        return $this;
    }

    public function addPrefix($table, $prefix)
    {
        $this->prefixes[$table] = $prefix;
        return $this;
    }

    /**
     * @param null $table
     * @param array|null $columns
     * @return \NilPortugues\Sql\QueryBuilder\Manipulation\Select
     */
    public function select($table = null, array $columns = null)
    {
        $query = parent::select($table, $columns);
        $query = $query->setTable($this->table);
        return $query;
    }

    /**
     * @param null $table
     * @param array|null $values
     * @return \NilPortugues\Sql\QueryBuilder\Manipulation\AbstractBaseQuery
     */
    public function insert($table = null, array $values = null)
    {
        $query = $query = parent::insert($table, $values);
        $query->setTable($this->table);
        return $query;
    }

    /**
     * @param null $table
     * @param array|null $values
     * @return \NilPortugues\Sql\QueryBuilder\Manipulation\AbstractBaseQuery
     */
    public function update($table = null, array $values = null)
    {
        $query = $query = parent::update($table, $values);
        $query->setTable($this->table);
        return $query;
    }

    /**
     * @param null $table
     * @return \NilPortugues\Sql\QueryBuilder\Manipulation\Delete
     */
    public function delete($table = null)
    {
        $query = $query = parent::delete($table);
        $query->setTable($this->table);
        return $query;
    }

    protected function getPrefix($table)
    {
        return Arr::get($this->prefixes, $table);
    }

    public function write(QueryInterface $query, $resetPlaceholders = true)
    {
        $content = parent::write($query, $resetPlaceholders);
        $content = preg_replace_callback("/([a-zA-Z0-9-_]+)\.([a-zA-Z0-9-_]+)/", function ($match) {
            $tablename = $match[1];
            $fieldName = $match[2];
            $prefix = $this->getPrefix($tablename);
            if (!Str::startWith($fieldName, $prefix)) {
                return "$tablename.{$prefix}_{$fieldName}";
            }
            return $match[0];
        }, $content);
        return $content;
    }

}