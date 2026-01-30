<?php
require __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;

$database = new Medoo([
    'type' => 'mysql',
    'host' => 'db',
    'database' => 'affinity',   // cambia al nombre real de tu BD
    'username' => 'affinity',
    'password' => 'affinity',
    'charset' => 'utf8mb4'
]);

// $database = new Medoo([
//     'type' => 'mysql',
//     'database' => 'dbs15060883',
//     'host' => 'db5019180537.hosting-data.io',
//     'username' => 'dbu3083527',
//     'password' => '4lk5PIQXQPj575spW9WW01BONst2IOc',
//     'charset' => 'utf8mb4'
// ]);