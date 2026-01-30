<?php

require __DIR__ . '/../api/db.php';

$datos = $database->select('config', '*');

header("Content-Type: application/json");

echo json_encode([
    "status" => "ok",
    "message" => "API funcionando",
    "content" => $datos
]);