<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id = $_POST['id_imagen'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

// Obtener la imagen antes de borrarla
$img = $database->get("imagenes", "*", ["id" => $id]);

if (!$img) {
    echo json_encode(["status" => "error", "msg" => "La imagen no existe"]);
    exit;
}

// Borrar archivo físico
$ruta = __DIR__ . '/../data/images/' . $img['nombre'];
if (file_exists($ruta)) unlink($ruta);

// Borrar de la BD
$database->delete("imagenes", ["id" => $id]);

echo json_encode(["status" => "ok"]);
