<?php

namespace Qwark\Orm\Model;

use NilPortugues\Sql\QueryBuilder\Manipulation\QueryInterface;
use Qwark\Tools\Str;

class GenericBuilder extends \NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder
{
    protected $prefix;
    protected $class;
    protected $table;

    public function __construct($prefix, $class, $table)
    {
        parent::__construct();
        $this->prefix = $prefix;
        $this->class = $class;
        $this->table = $table;
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


    public function write(QueryInterface $query, $resetPlaceholders = true)
    {
        $content = parent::write($query, $resetPlaceholders);
        $content = preg_replace_callback("/{$this->table}\.([a-zA-Z0-9-_]+)/", function ($match) {
            if (!Str::startWith($match[1], $this->prefix)) {
                return "{$this->table}.{$this->prefix}_{$match[1]}";
            }
            return $match[0];
        }, $content);
        return $content;
    }

}