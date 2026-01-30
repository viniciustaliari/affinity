<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

// 1. Buscar promoción
$promo = $database->get("promociones", "*", [
    "id" => $id
]);

if (!$promo) {
    echo json_encode(["status" => "error", "msg" => "Promoción no encontrada"]);
    exit;
}

// 2. Eliminar imagen física
if (!empty($promo['dir_imagen'])) {
    $ruta = __DIR__ . '/../data/promos/' . $promo['dir_imagen'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }
}

// 3. Eliminar registro de la BDD
$database->delete("promociones", [
    "id" => $id
]);

echo json_encode(["status" => "ok"]);
