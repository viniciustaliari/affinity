<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/media_filename.php';
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

$mime = detectUploadMimeType($file);
if (!isImageMime($mime)) {
    echo json_encode(["status" => "error", "msg" => "El archivo no es una imagen valida"]);
    exit;
}

$ext = normalizeMediaExtension($mime, (string)($file["name"] ?? ""), 'image');
$nombreFinal = uniqid("img_", true) . "." . $ext;
$nombreOriginal = sanitizeClientMediaFileName((string)($file["name"] ?? ""), $nombreFinal);

$rutaDestino = __DIR__ . '/../data/images/' . $nombreFinal;

if (!move_uploaded_file($file["tmp_name"], $rutaDestino)) {
    echo json_encode(["status" => "error", "msg" => "No se pudo guardar la imagen"]);
    exit;
}

/* Insert extendido (compatible hacia atrás) */
$database->insert("imagenes", [
    "nombre"      => $nombreFinal,
    "nombre_original" => $nombreOriginal,
    "duracion"    => $duracion,
    "indice"      => $indice,
    "fit"         => $fit,
    "transition"  => $transition,
    "id_programa" => $id_programa
]);

echo json_encode(["status" => "ok"]);
