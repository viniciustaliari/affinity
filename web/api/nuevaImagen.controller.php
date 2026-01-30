<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id_programa = $_POST['id_programa'] ?? null;
$duracion    = floatval($_POST['duracion'] ?? 0);
$indice      = intval($_POST['indice'] ?? 0);
$fit         = $_POST['fit'] ?? 'cover';
$transition  = $_POST['transition'] ?? 'fade';
$file        = $_FILES['imagen'] ?? null;

if (!$id_programa || !$file) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "msg" => "Error en el archivo"]);
    exit;
}

$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$nombreFinal = uniqid("img_", true) . "." . $ext;

$rutaDestino = __DIR__ . '/../data/images/' . $nombreFinal;

if (!move_uploaded_file($file["tmp_name"], $rutaDestino)) {
    echo json_encode(["status" => "error", "msg" => "No se pudo guardar la imagen"]);
    exit;
}

/* Insert extendido (compatible hacia atrás) */
$database->insert("imagenes", [
    "nombre"      => $nombreFinal,
    "duracion"    => $duracion,
    "indice"      => $indice,
    "fit"         => $fit,
    "transition"  => $transition,
    "id_programa" => $id_programa
]);

echo json_encode(["status" => "ok"]);
