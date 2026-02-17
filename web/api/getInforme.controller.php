<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/contextos.php';

use Medoo\Medoo;

header('Content-Type: application/json');

$ctx = isset($_GET['ctx']) ? intval($_GET['ctx']) : 0;
if ($ctx <= 0) {
    echo json_encode(["error" => "Falta ctx"]);
    exit;
}

$contexto = $database->get("contextos", ["id", "nombre"], ["id" => $ctx]);
if (!$contexto) {
    echo json_encode(["error" => "Contexto invalido"]);
    exit;
}

$servicioId = getServicioIdPorContextoId($ctx);
$servicio = null;

if ($servicioId !== null) {
    $servicio = $database->get("servicios", "*", ["id" => $servicioId]);
}

$totalHoy = 0.0;
$totalMes = 0.0;
$totalMesAnterior = 0.0;
$datosDiarios = [];

if ($servicio) {
    $hoy = date("Y-m-d");
    $inicioMes = date("Y-m-01");
    $finMes = date("Y-m-t");
    $inicioMesAnterior = date("Y-m-01", strtotime("-1 month"));
    $finMesAnterior = date("Y-m-t", strtotime("-1 month"));

    $usosHoy = $database->count("historial_servicios", [
        "id_servicio" => $servicioId,
        "fecha[>=]" => "$hoy 00:00:00",
        "fecha[<=]" => "$hoy 23:59:59"
    ]);

    $usosMes = $database->count("historial_servicios", [
        "id_servicio" => $servicioId,
        "fecha[>=]" => "$inicioMes 00:00:00",
        "fecha[<=]" => "$finMes 23:59:59"
    ]);

    $usosMesAnt = $database->count("historial_servicios", [
        "id_servicio" => $servicioId,
        "fecha[>=]" => "$inicioMesAnterior 00:00:00",
        "fecha[<=]" => "$finMesAnterior 23:59:59"
    ]);

    $precio = (float)($servicio["precio"] ?? 0);
    $totalHoy = $usosHoy * $precio;
    $totalMes = $usosMes * $precio;
    $totalMesAnterior = $usosMesAnt * $precio;

    $datosDiarios = $database->select("historial_servicios", [
        "dia" => Medoo::raw("DATE(fecha)"),
        "conteo" => Medoo::raw("COUNT(*)")
    ], [
        "id_servicio" => $servicioId,
        "fecha[>=]" => "$inicioMes 00:00:00",
        "fecha[<=]" => "$finMes 23:59:59",
        "GROUP" => Medoo::raw("DATE(fecha)")
    ]);
}

if (!$servicio) {
    $servicio = [
        "id" => $servicioId,
        "nombre" => $contexto["nombre"],
        "precio" => 0
    ];
}

$programaActivo = $database->get("programas", "*", [
    "contexto" => $ctx
]);

echo json_encode([
    "status" => "ok",
    "contexto" => $contexto,
    "servicio" => $servicio,
    "totalHoy" => $totalHoy,
    "totalMes" => $totalMes,
    "totalMesAnterior" => $totalMesAnterior,
    "programaActivo" => $programaActivo,
    "datosDiarios" => $datosDiarios
]);
