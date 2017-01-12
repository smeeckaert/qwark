<?php

namespace Qwark\Orm;


use NilPortugues\Sql\QueryBuilder\Manipulation\QueryInterface;
use Qwark\Orm\Model\GenericBuilder;

abstract class Model
{
    static protected $_id = 'id';
    static protected $_table;
    static protected $_prefix;
    static protected $_unique;
    protected static $_relations = array();
    protected $_prefixLen;
    protected $_relationships = array();
    protected static $_builder;


    public function __construct($data = null)
    {
        if (!empty($data)) {
            $this->import($data);
        }
        static::init();
    }

    protected static function init()
    {
        if (empty(static::$_table)) {
            static::$_table = Tools::serializeString(get_called_class());
        }
        if (empty(static::$_prefix)) {
            static::$_prefix = Tools::adaptPrefix(static::$_table);
        }
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
     * @param int|array|QueryInterface $properties
     * @param bool $forceArray
     * @return static[]|static
     */
    public static function find($properties, $forceArray = false)
    {
        static::init();
        if (empty($builder)) {
            $builder = DB::instance()->builder();
        }
        $idField = static::propToDb(static::_id());
        if ($properties instanceof QueryInterface) {
            $builder = $properties->getBuilder();
            $query = $properties;
        } else {
            $query = $builder->select()->setTable(static::$_table);
            if (!is_array($properties)) {
                $query->where()->equals($idField, $properties);
            } else {
                foreach ($properties as $key => $value) {
                    $query->where()->equals(static::propToDb($key), $value);
                }
            }
        }

        $sql = $builder->writeFormatted($query);
        $result = DB::prepare($sql, $builder->getValues());
        $results = array();
        // @todo change that to an iterable collection
        while (($row = $result->fetch(\PDO::FETCH_ASSOC))) {
            $results[$row[$idField]] = new static($row);
        }
        if (!$forceArray && $result->rowCount() == 1) {
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
        if (empty(static::$_builder)) {
            static::$_builder = new GenericBuilder(static::$_prefix, get_called_class(), static::$_table);
        }
        return static::$_builder;
    }

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
                $this->_relationships[$name] = $model::find($params, true);
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

    protected function dbToProp($field)
    {
        if (empty($this->_prefixLen)) {
            $this->_prefixLen = strlen(static::$_prefix) + 1;
        }
        return substr($field, $this->_prefixLen);
    }

    public static function propToDb($field)
    {
        return static::$_prefix . '_' . $field;
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

    protected function before_save()
    {
    }

    protected function after_save()
    {
    }

    /**
     * Save into the schema
     */
    public function save()
    {
        $this->before_save();
        $idField = static::_id();
        if (empty($this->$idField)) {
            $this->$idField = $this->insert();
        } else {
            $this->update();
        }
        $this->after_save();
    }

    /**
     * @todo remove or rename
     * @return mixed|null
     */
    protected function getIdValue()
    {
        $idField = static::_id();
        return $this->$idField;
    }

    public function update()
    {
        $fields = $this->getFields();
        unset($fields[static::_id()]);
        $data = static::keysToDb($fields);
        $builder = static::builder();
        $query = $builder->update()->setValues($data);
        $query->where()->equals(static::_id(), $this->getIdValue());
        DB::prepare($builder->write($query), $builder->getValues());
    }

    public function delete()
    {
        $builder = static::builder();
        $query = $builder->delete()->where()->equals(static::_id(), $this->getIdValue())->end();
        $query = $builder->write($query);
        DB::prepare($builder->write($query), $builder->getValues());
    }

    protected function insert()
    {
        $data = static::keysToDb($this->getFields());
        $builder = static::builder();
        $query = $builder->insert()->setValues($data);
        DB::prepare($builder->write($query), $builder->getValues());
        return DB::lastId();
    }

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
     * @param       $array
     *
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