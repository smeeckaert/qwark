<?php

namespace Qwark\Tools {

    class Debug
    {
        /**
         * Debug a variable and exit
         * @param $data
         */
        public static function dd($data = null)
        {
            die(static::d($data));
        }

        /**
         * Debug a variable
         * @param $data
         */
        public static function d($data = null)
        {
            echo var_dump($data);
        }

        /**
         * Register debug functions in the global namespace
         */
        public static function init()
        {
        }

    }
}

/**
 * Declare functions in the global namespace
 */
namespace {

    use Qwark\Tools\Debug;

    /**
     * Debug a variable
     * @param $data
     */
    function d($data)
    {
        Debug::d($data);
    }

    /**
     * Debug a variable and exit
     * @param $data
     */
    function dd($data)
    {
        Debug::dd($data);
    }
}