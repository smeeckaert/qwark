<?php

namespace Qwark\Tools {

    class Debug
    {
        public static function dd($data)
        {
            die(static::d($data));
        }

        public static function d($data)
        {
            if (!empty($data)) {
                echo var_dump($data);
            }
        }

        public static function init()
        {
        }

    }
}

namespace {

    use Qwark\Tools\Debug;

    function d($data)
    {
        Debug::d($data);
    }

    function dd($data)
    {
        Debug::dd($data);
    }
}