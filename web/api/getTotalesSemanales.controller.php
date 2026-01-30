<?php
require_once __DIR__ . "/db.php";
header("Content-Type: application/json");

$hoy    = date("Y-m-d") . " 23:59:59";
$hace7  = date("Y-m-d", strtotime("-7 days")) . " 00:00:00";

$servicios = $database->select("servicios", ["id", "nombre", "precio"]);

$datos = [];

foreach ($servicios as $s) {

    // Contamos registros de los últimos 7 días
    $conteo = $database->count("historial_servicios", [
        "id_servicio" => $s["id"],
        "fecha[>=]"   => $hace7,
        "fecha[<=]"   => $hoy
    ]);

    $total = $conteo * $s["precio"];

    $datos[] = [
        "servicio" => $s["nombre"],
        "precio_unitario" => $s["precio"],
        "usos" => $conteo,
        "total" => $total
    ];
}

echo json_encode([
    "status" => "ok",
    "datos"  => $datos
]);
