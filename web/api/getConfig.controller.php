<?php

require __DIR__ . '/../api/db.php';

// Traer id y estado de cada configuración
$datos = $database->select('config', ['id', 'estado']);

// Convertir a un array accesible por id:
// $config[1], $config[2], $config[3]
$config = [];

foreach ($datos as $d) {
    $config[$d['id']] = (int) $d['estado'];
}