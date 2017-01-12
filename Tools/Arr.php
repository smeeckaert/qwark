<?php

namespace Qwark\Tools;

class Arr
{
    /**
     * Return a element from an array if it is set, otherwise returns $default
     *
     * @param $array
     * @param $var
     * @param null $default
     * @return mixed
     */
    public static function get($array, $var, $default = null)
    {
        if (isset($array[$var])) {
            return $array[$var];
        }
        return $default;
    }

    /**
     * Merge recursively $arrayMaster into $array
     *
     * @param $array array
     * @param $arrayMaster array
     * @return mixed
     */
    public static function merge($array, $arrayMaster)
    {
        /**
         * Find all existing keys in both $array and $arrayMaster and merge them
         */
        foreach ($array as $key => $value) {
            if (isset($arrayMaster[$key])) {
                if (is_array($value)) {
                    $value = static::merge($value, $arrayMaster[$key]);
                }
                $arrayMaster[$key] = $value;
            }
        }
        foreach ($arrayMaster as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    /**
     * Pick the $key value of an array or object.
     * The index of the newly created array can be picked with $indexKey
     *
     * @param      $array
     * @param      $key
     * @param null $indexKey
     *
     * @return mixed
     */
    public static function pick($array, $key, $indexKey = null)
    {
        $return = array();
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        } elseif (is_object($array) && isset($array->$key)) {
            return $array->$key;
        }
        foreach ($array as $item) {
            $value = static::pick($item, $key);
            if (!empty($indexKey)) {
                $return[static::pick($item, $indexKey)] = $value;
            } else {
                $return[] = $value;
            }
        }
        return $return;
    }
}