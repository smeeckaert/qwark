<?php

require_once '../../../vendor/autoload.php';
\Qwark\Orm\DB::init('main', 'mysql:dbname=qwark_main;host=localhost', 'root', 'root');
\Qwark\Orm\DB::init('replica', 'mysql:dbname=qwark_replica;host=localhost', 'root', 'root');

\Qwark\Tools\Debug::init();

$isCli = (php_sapi_name() === 'cli');
error_reporting(E_ALL);

if (!$isCli) {
    echo '<pre>';
}
