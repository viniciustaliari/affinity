<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id = $_POST['id_video'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

// Obtener el video antes de borrar
$vid = $database->get("video", "*", ["id" => $id]);

if (!$vid) {
    echo json_encode(["status" => "error", "msg" => "El video no existe"]);
    exit;
}

// Borrar archivo físico
$ruta = __DIR__ . '/../data/videos/' . $vid['nombre'];
if (file_exists($ruta)) unlink($ruta);

// Borrar de BD
$database->delete("video", ["id" => $id]);

echo json_encode(["status" => "ok"]);
