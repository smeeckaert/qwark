<?php
require_once 'bootstrap.php';

try {
    $data = \Qwark\Orm\DB::query('SELECT * FROM post');
    if (!empty($data)) {
        $data = $data->fetchAll(\PDO::FETCH_ASSOC);
    }
    var_dump($data);

} catch (Exception $e) {
    d($e->getMessage());
}