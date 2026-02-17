<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';
require_once __DIR__ . '/media_filename.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

$contexto = $_POST['contexto'] ?? null;
$nombre   = $_POST['nombre'] ?? null;
$texto    = $_POST['texto'] ?? "";

if (!$contexto || !$nombre) {
    echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios"]);
    exit;
}

/* =========================================================
   1) Crear programa
   ========================================================= */

$database->insert("programas", [
    "nombre"   => $nombre,
    "contexto" => $contexto,
    "texto"    => $texto
]);

$id_programa = $database->id();

if (!$id_programa) {
    echo json_encode(["status" => "error", "mensaje" => "No se pudo crear el programa"]);
    exit;
}

/* =========================================================
   2) Guardar IMÁGENES (MISMA lógica de subida)
   ========================================================= */

$dirImg = __DIR__ . '/../data/images/';
if (!is_dir($dirImg)) {
    mkdir($dirImg, 0777, true);
}

foreach ($_FILES as $key => $file) {

    if (!preg_match('/imagen_(\d+)/', $key, $match)) {
        continue;
    }

    $i = $match[1];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        continue;
    }

    $duracion   = floatval($_POST["duracionImg_$i"] ?? 1);
    $indice     = intval($_POST["indiceImg_$i"] ?? 0);
    $fit        = $_POST["fitImg_$i"] ?? 'cover';
    $transition = $_POST["transitionImg_$i"] ?? 'fade';

    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $nombreFinal = uniqid("img_", true) . "." . $ext;
    $nombreOriginal = sanitizeClientMediaFileName((string)($file["name"] ?? ""), $nombreFinal);

    move_uploaded_file($file["tmp_name"], $dirImg . $nombreFinal);

    $database->insert("imagenes", [
        "nombre"      => $nombreFinal,
        "nombre_original" => $nombreOriginal,
        "duracion"    => $duracion,
        "indice"      => $indice,
        "fit"         => $fit,
        "transition"  => $transition,
        "id_programa" => $id_programa
    ]);
}

/* =========================================================
   3) Guardar VIDEOS (MISMA lógica de subida)
   ========================================================= */

$dirVid = __DIR__ . '/../data/videos/';
if (!is_dir($dirVid)) {
    mkdir($dirVid, 0777, true);
}

foreach ($_FILES as $key => $file) {

    if (!preg_match('/video_(\d+)/', $key, $match)) {
        continue;
    }

    $i = $match[1];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        continue;
    }

    $duracion = floatval($_POST["duracionVideo_$i"] ?? 1);
    $indice   = intval($_POST["indiceVideo_$i"] ?? 0);
    $repeat   = isset($_POST["repeatVideo_$i"]) ? intval($_POST["repeatVideo_$i"]) : null;
    $mute     = isset($_POST["muteVideo_$i"]) ? intval($_POST["muteVideo_$i"]) : 0;

    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $nombreFinal = uniqid("vid_", true) . "." . $ext;
    $nombreOriginal = sanitizeClientMediaFileName((string)($file["name"] ?? ""), $nombreFinal);

    move_uploaded_file($file["tmp_name"], $dirVid . $nombreFinal);

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
}

/* =========================================================
   4) Generar ZIP y persistirlo en base de datos
   ========================================================= */

require_once __DIR__ . '/programa_package.php';
$storedPackage = saveProgramaZipSnapshotToDatabase((int)$id_programa);

if (($storedPackage["status"] ?? "") !== "ok") {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Programa guardado, pero no se pudo crear/persistir el ZIP.",
        "id_programa" => $id_programa,
        "package_error" => (string)($storedPackage["error"] ?? "unknown")
    ]);
    exit;
}

/* =========================================================
   RESPUESTA FINAL
   ========================================================= */

echo json_encode([
    "status"      => "ok",
    "mensaje"     => "Programa creado correctamente",
    "id_programa" => $id_programa,
    "package_saved" => true,
    "package_id" => (int)($storedPackage["package_id"] ?? 0),
    "package_version" => (int)($storedPackage["version"] ?? 0),
    "zip_name" => (string)($storedPackage["zip_name"] ?? ""),
    "zip_size_bytes" => (int)($storedPackage["zip_size_bytes"] ?? 0)
]);
