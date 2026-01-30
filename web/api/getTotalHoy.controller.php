<?php
require __DIR__ . '/db.php';

header('Content-Type: application/json');

// Fecha de hoy
$hoy = date("Y-m-d");

// Consulta
$rows = $database->query("
    SELECT SUM(s.precio) AS total
    FROM historial_servicios h
    JOIN servicios s ON h.id_servicio = s.id
    WHERE DATE(h.fecha) = '$hoy'
")->fetchAll();

$total = $rows[0]['total'] ?? 0;

echo json_encode([
    "status" => "ok",
    "totalHoy" => floatval($total)
]);
