<?php

require __DIR__ . '/../api/db.php';

// Traer id y precio
$datos = $database->select('servicios', ['id', 'nombre', 'precio']);

// Convertir a array accesible por id
$prices = [];
foreach ($datos as $p) {
    $prices[$p['id']] = $p['precio'];
}