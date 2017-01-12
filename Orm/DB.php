<?php

namespace Qwark\Orm;

use Qwark\Orm\Database;

class DB
{
    /** @var  Database\Link[] */
    protected static $instances;
    /** @var  Database\Link */
    protected static $currentInstance;

    /**
     * Alias for i()
     *
     * @return Database\Link
     */
    public static function instance($name = null)
    {
        if ($name === null) {
            return static::$currentInstance;
        }
        return static::$instances[$name];
    }

    /**
     * Change the current default instance
     * @param $name
     */
    public static function setCurrent($name)
    {
        static::$currentInstance = static::instance($name);
    }

    /**
     * Call this method to instianciated the database connection
     *
     * @param string $name
     * @param      $dsn
     * @param null $user
     * @param null $password
     * @param null $options
     */
    public static function init($name, $dsn, $user = null, $password = null, $options = null)
    {
        if (empty(static::$instances[$name])) {
            static::$instances[$name] = new Database\Link($dsn, $user, $password, $options, $name);
            if (empty(static::$currentInstance)) {
                static::$currentInstance = static::$instances[$name];
            }
        }
    }

    public static function prepare($sql, $values, $name = null)
    {
        return static::instance($name)->prepare($sql, $values);
    }

    public static function query($sql, $name = null)
    {
        return static::instance($name)->query($sql);
    }

    public static function lastId($name = null)
    {
        return static::instance($name)->lastInsertId();
    }


}