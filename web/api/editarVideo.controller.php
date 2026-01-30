<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id       = $_POST['id_video'] ?? null;
$duracion = $_POST['duracion'] ?? null;
$indice   = $_POST['indice'] ?? null;
$repeat   = $_POST['repeat'] ?? null;
$mute     = $_POST['mute'] ?? null;

if (!$id || $duracion === null) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$data = [
    "duracion" => floatval($duracion)
];

/* Campos opcionales (solo si vienen) */
if ($indice !== null) {
    $data["indice"] = intval($indice);
}

if ($repeat !== null && $repeat !== '') {
    $data["repeat"] = intval($repeat);
}

if ($mute !== null) {
    $data["mute"] = intval($mute);
}

$database->update("video", $data, [
    "id" => $id
]);

echo json_encode(["status" => "ok"]);
