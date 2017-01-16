<?php


namespace Qwark\Orm;

class Tools
{
    public static function serializeString($string)
    {
        return mb_strtolower(str_replace([' '], '_', $string));
    }

    public static function adaptPrefix($name)
    {
        return substr($name, 0, 4);
    }

    public static function assocTableName($from, $to)
    {
        return implode("_", [$from, $to]);
    }

    public static function assocPrefix($from, $to)
    {
        return substr($from, 0, 2) . substr($to, 0, 2);
    }
}