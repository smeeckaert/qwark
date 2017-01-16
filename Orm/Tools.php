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
}