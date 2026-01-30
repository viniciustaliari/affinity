<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id        = $_POST['id_imagen'] ?? null;
$duracion  = $_POST['duracion'] ?? null;
$indice    = $_POST['indice'] ?? null;
$fit       = $_POST['fit'] ?? null;
$transition= $_POST['transition'] ?? null;

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

if ($fit !== null) {
    $data["fit"] = $fit;
}

if ($transition !== null) {
    $data["transition"] = $transition;
}

$database->update("imagenes", $data, [
    "id" => $id
]);

echo json_encode(["status" => "ok"]);
