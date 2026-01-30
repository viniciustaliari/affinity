<?php
require __DIR__ . '/db.php';
header("Content-Type: application/json");

// HOY
$hoy_inicio = date("Y-m-d 00:00:00");
$hoy_fin    = date("Y-m-d 23:59:59");

// AYER
$ayer_inicio = date("Y-m-d 00:00:00", strtotime("-1 day"));
$ayer_fin    = date("Y-m-d 23:59:59", strtotime("-1 day"));

// SUMA HOY
$hoy = $database->sum("historial_servicios", [
    "[><]servicios" => ["id_servicio" => "id"]
], "servicios.precio", [
    "fecha[<>]" => [$hoy_inicio, $hoy_fin]
]);

// SUMA AYER
$ayer = $database->sum("historial_servicios", [
    "[><]servicios" => ["id_servicio" => "id"]
], "servicios.precio", [
    "fecha[<>]" => [$ayer_inicio, $ayer_fin]
]);

echo json_encode([
    "status" => "ok",
    "hoy" => $hoy ?: 0,
    "ayer" => $ayer ?: 0
]);
