<?php

namespace Qwark\Tools;

class Str
{
    public static function startWith($str, $start, $caseSensitive = true)
    {
        return (bool)preg_match('/^' . preg_quote($start, '/') . '/m' . (!$caseSensitive ? 'i' : ''), $str);
    }
}