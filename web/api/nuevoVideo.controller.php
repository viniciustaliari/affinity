<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/media_filename.php';
header('Content-Type: application/json');

$id_programa = $_POST['id_programa'] ?? null;
$duracion    = floatval($_POST['duracion'] ?? 0);
$indice      = intval($_POST['indice'] ?? 0);
$repeat      = isset($_POST['repeat']) ? intval($_POST['repeat']) : null;
$mute        = isset($_POST['mute']) ? intval($_POST['mute']) : 0;
$file        = $_FILES['video'] ?? null;

if (!$id_programa || !$file) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "msg" => "Error en el archivo"]);
    exit;
}

$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$nombreFinal = uniqid("vid_", true) . "." . $ext;
$nombreOriginal = sanitizeClientMediaFileName((string)($file["name"] ?? ""), $nombreFinal);

$rutaDestino = __DIR__ . '/../data/videos/' . $nombreFinal;

if (!move_uploaded_file($file["tmp_name"], $rutaDestino)) {
    echo json_encode(["status" => "error", "msg" => "No se pudo guardar el video"]);
    exit;
}

/* Insert extendido (compatible hacia atrás) */
$database->insert("video", [
    "nombre"      => $nombreFinal,
    "nombre_original" => $nombreOriginal,
    "duracion"    => $duracion,
    "indice"      => $indice,
    "repeat"      => $repeat,
    "mute"        => $mute,
    "start_ms"    => 0,
    "end_ms"      => null,
    "id_programa" => $id_programa
]);

echo json_encode(["status" => "ok"]);
