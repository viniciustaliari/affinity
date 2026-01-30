<?php
require_once __DIR__ . '/db.php';
use Medoo\Medoo;

header('Content-Type: application/json');

$ctx = $_GET['ctx'] ?? null;

if (!$ctx) {
    echo json_encode(["error" => "Falta ctx"]);
    exit;
}

$servicioPorContexto = [
    1 => 1,
    2 => 2,
    3 => 3,
    4 => 4,
    5 => 5
];

if (!isset($servicioPorContexto[$ctx])) {
    echo json_encode(["error" => "Contexto inválido"]);
    exit;
}

$id_servicio = $servicioPorContexto[$ctx];

// Obtener servicio
$servicio = $database->get("servicios", "*", ["id" => $id_servicio]);

// ------------------------
// TOTAL HOY
// ------------------------
$hoy = date("Y-m-d");

$usosHoy = $database->count("historial_servicios", [
    "id_servicio" => $id_servicio,
    "fecha[>=]" => "$hoy 00:00:00",
    "fecha[<=]" => "$hoy 23:59:59"
]);

$totalHoy = $usosHoy * $servicio['precio'];

// ------------------------
// TOTAL MES
// ------------------------
$inicioMes = date("Y-m-01");
$finMes    = date("Y-m-t");

$usosMes = $database->count("historial_servicios", [
    "id_servicio" => $id_servicio,
    "fecha[>=]" => "$inicioMes 00:00:00",
    "fecha[<=]" => "$finMes 23:59:59"
]);

$totalMes = $usosMes * $servicio['precio'];

// ------------------------
// MES ANTERIOR
// ------------------------
$inicioMesAnterior = date("Y-m-01", strtotime("-1 month"));
$finMesAnterior    = date("Y-m-t",  strtotime("-1 month"));

$usosMesAnt = $database->count("historial_servicios", [
    "id_servicio" => $id_servicio,
    "fecha[>=]" => "$inicioMesAnterior 00:00:00",
    "fecha[<=]" => "$finMesAnterior 23:59:59"
]);

$totalMesAnterior = $usosMesAnt * $servicio['precio'];

// ------------------------
// PROGRAMA ACTIVO
// ------------------------
$programaActivo = $database->get("programas", "*", [
    "contexto" => $ctx
]);

// ------------------------
// DATOS DIARIOS PARA CHART
// ------------------------
$datosDiarios = $database->select("historial_servicios", [
    "dia" => Medoo::raw("DATE(fecha)"),
    "conteo" => Medoo::raw("COUNT(*)")
], [
    "id_servicio" => $id_servicio,
    "fecha[>=]" => "$inicioMes 00:00:00",
    "fecha[<=]" => "$finMes 23:59:59",
    "GROUP" => Medoo::raw("DATE(fecha)")
]);

// ------------------------
// RESPUESTA FINAL
// ------------------------
echo json_encode([
    "status" => "ok",
    "servicio" => $servicio,
    "totalHoy" => $totalHoy,
    "totalMes" => $totalMes,
    "totalMesAnterior" => $totalMesAnterior,
    "programaActivo" => $programaActivo,
    "datosDiarios" => $datosDiarios
]);
