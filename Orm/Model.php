<?php

namespace Qwark\Orm;


use NilPortugues\Sql\QueryBuilder\Manipulation\QueryInterface;
use Qwark\Orm\Model\GenericBuilder;

abstract class Model
{
    static protected $_id = 'id';
    static protected $_table;
    static protected $_prefix;

    /**
     * These two variables are share by every model, so they can't be overriden
     */
    /** @var  string[] */
    static protected $_tableList;
    /** @var  string[] */
    static protected $_prefixList;
    /** @var  string[] */
    static protected $_prefixLen;
    /** @todo refacto */
    protected static $_relations = array();
    protected $_relationships = array();
    /** @var  GenericBuilder[] The query builder for this model */
    protected static $_builder;
    /** @var string Name of the DB instance the model is fetched from */
    protected $_dbName;

    /**
     * Model constructor.
     * @param null $data An associative array of [database_column_name_with_prefix => value]
     * @param null $dbName The name of the database it was fetch from
     */
    public function __construct($data = null, $dbName = null)
    {
        static::init();
        if (!empty($data)) {
            $this->import($data);
        }
        $this->_dbName = $dbName;
    }

    /**
     * Set the default table and prefix values
     */
    protected static function init()
    {
        if (empty(static::$_tableList[get_called_class()])) {
            $tableName = !empty(static::$_table) ? static::$_table : Tools::serializeString(get_called_class());
            static::setTable($tableName);
        }
        if (empty(static::$_prefixList[get_called_class()])) {
            $prefix = !empty(static::$_prefix) ? static::$_prefix : Tools::adaptPrefix(static::$_tableList[get_called_class()]);
            static::setPrefix($prefix);
        }
    }

    /**
     * @param $table
     */
    protected static function setTable($table)
    {
        static::$_tableList[get_called_class()] = $table;
    }

    /**
     * @param $prefix
     */
    protected static function setPrefix($prefix)
    {
        static::$_prefixList[get_called_class()] = $prefix;
    }

    /**
     * @param $len
     */
    protected static function setPrefixLen($len)
    {
        static::$_prefixLen[get_called_class()] = $len;
    }

    /**
     * Return the model's table
     * @return string
     */
    public static function table()
    {
        static::init();
        return static::$_tableList[get_called_class()];
    }

    /**
     * Return the model's prefix
     * @return string
     */
    public static function prefix()
    {
        static::init();
        return static::$_prefixList[get_called_class()];
    }

    /**
     * Return the length of the table prefix, including the _ sign
     * @return string
     */
    public static function prefixLen()
    {
        if (empty(static::$_prefixLen[get_called_class()])) {
            static::setPrefixLen(strlen(static::prefix()) + 1);
        }
        return static::$_prefixLen[get_called_class()];
    }

    /**
     * Return the unprefixed name of the primary key
     * @return string
     */
    protected static function _id()
    {
        return static::$_id;
    }

    /**
     * Find items from its id, or properties or a query
     *
     * @param $properties
     * @param null $dbName
     * @return static[]|static
     */
    public static function find($properties, $dbName = null)
    {
        static::init();
        $idField = static::propToDb(static::_id());

        if ($properties instanceof QueryInterface) {
            $builder = $properties->getBuilder();
            $query = $properties;
        } else {
            $builder = DB::instance($dbName)->builder();
            $query = $builder->select()->setTable(static::table());
            if (!is_array($properties)) {
                $query->where()->equals($idField, $properties);
            } else {
                foreach ($properties as $key => $value) {
                    $query->where()->equals(static::propToDb($key), $value);
                }
            }
        }

        $sql = $builder->writeFormatted($query);
        $result = DB::prepare($sql, $builder->getValues(), $dbName);
        $results = array();
        // @todo change that to an iterable collection
        while (($row = $result->fetch(\PDO::FETCH_ASSOC))) {
            d($row);
            $results[$row[$idField]] = new static($row, DB::instance($dbName)->name());
        }
        // @todo probably remove that
        if ($result->rowCount() == 1) {
            return current($results);
        }
        return $results;
    }

    /**
     * @return GenericBuilder
     */
    public static function builder()
    {
        static::init();
        if (empty(static::$_builder[get_called_class()])) {
            static::$_builder[get_called_class()] = new GenericBuilder(static::prefix(), get_called_class(), static::table());
        }
        return static::$_builder[get_called_class()];
    }

    /**
     * @todo refacto
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, static::$_relations)) {
            if (!isset($this->_relationships[$name])) {
                $rel = static::$_relations[$name];
                $fromProp = $rel['from'];
                /** @var Model $model */
                $model = $rel['model'];
                $params = array('and_where' => array($rel['to'] => $this->$fromProp));
                if (!empty($rel['conditions'])) {
                    $params = Tools::deepMerge($params, $rel['conditions']);
                }
                $this->_relationships[$name] = $model::find($params);
            }
            return $this->_relationships[$name];
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**
     * @todo refacto
     * @param $relation
     *
     * @return Model
     */
    public function rel($relation)
    {
        if (array_key_exists($relation, static::$_relations)) {
            $rel = static::$_relations[$relation];
            $fromProp = $rel['from'];
            $toProp = $rel['to'];

            /** @var Model $model */
            $model = $rel['model'];
            $item = new $model();
            $item->$toProp = $this->$fromProp;
            return $item;
        }
        return null;
    }


    /**
     * Remove the prefix from a field
     * @param $field
     * @return string
     */
    protected function dbToProp($field)
    {
        return substr($field, static::prefixLen());
    }

    /**
     * Add the prefix to a field name
     * @param $field
     * @return string
     */
    public static function propToDb($field)
    {
        static::init();
        return static::prefix() . '_' . $field;
    }


    /**
     * Import an array of databases rows into model properties
     *
     * @param $data
     */
    public function import($data)
    {
        foreach ($data as $key => $value) {
            $prop = $this->dbToProp($key);
            $this->$prop = $value;
        }
    }

    /**
     * @todo change this behaviour somehow
     */

    /**
     * Called before saving an element
     */
    protected function before_save()
    {
    }

    /**
     * Called after saving an element
     */
    protected function after_save()
    {
    }

    /**
     * Save into the schema
     */
    public function save($dbName = null)
    {
        $this->before_save();
        $idField = static::_id();
        if (empty($this->$idField)) {
            $this->$idField = $this->insert($dbName);
        } else {
            $this->update($dbName);
        }
        $this->after_save();
    }

    /**
     * @return mixed|null
     */
    protected function getIdValue()
    {
        $idField = static::_id();
        return $this->$idField;
    }

    /**
     * @param null $dbName
     * @return string
     */
    protected function insert($dbName = null)
    {
        $data = static::keysToDb($this->getFields());
        $builder = static::builder();
        $query = $builder->insert()->setValues($data);
        DB::prepare($builder->write($query), $builder->getValues(), $dbName);
        $this->_dbName = DB::instance($dbName)->name();
        return DB::lastId();
    }

    /**
     * @param null $dbName
     * @return string
     */
    protected function update($dbName = null)
    {
        $dbName = DB::instance($dbName)->name();
        $id = $this->getIdValue();
        /**
         * If we try to save the item in an other db that the one it was fetched from,
         * we need to check if the item exists already, if it does not, we insert it instead
         */
        if ($this->_dbName != $dbName) {
            $item = static::find($id, $dbName);
            if (empty($item)) {
                return $this->insert($dbName);
            }
        }
        $fields = $this->getFields();
        unset($fields[static::_id()]);
        $data = static::keysToDb($fields);
        $builder = static::builder();
        $query = $builder->update()->setValues($data);
        $query->where()->equals(static::_id(), $id);
        DB::prepare($builder->write($query), $builder->getValues(), $dbName);
        $this->_dbName = DB::instance($dbName)->name();
    }

    /**
     * @param null $dbName
     */
    public function delete($dbName = null)
    {
        $builder = static::builder();
        $query = $builder->delete()->where()->equals(static::_id(), $this->getIdValue())->end();
        $query = $builder->write($query);
        DB::prepare($builder->write($query), $builder->getValues(), $dbName);
    }

    /**
     * Return all the properties of the object that is a database field (ie. doesn't start with _)
     * @return array
     */
    protected function getFields()
    {
        $properties = get_object_vars($this);
        $dbFields = array();
        foreach ($properties as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            $dbFields[$key] = $value;
        }
        return $dbFields;
    }

    /**
     * Add the prefix on each keys of an array
     * @param $array
     * @return array
     */
    protected static function keysToDb($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        $db = array();
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $db[static::propToDb($key)] = static::keysToDb($value);
            } else {
                $db[$key] = static::keysToDb($value);
            }
        }
        return $db;
    }
}