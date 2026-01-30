<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$items = $_POST['items'] ?? null;

if (!$items || !is_array($items)) {
    echo json_encode(["status" => "error", "msg" => "Datos inválidos"]);
    exit;
}

foreach ($items as $item) {
    $id    = intval($item['id']);
    $tipo  = $item['tipo'];
    $indice= intval($item['indice']);

    if ($tipo === 'imagen') {
        $database->update("imagenes", ["indice" => $indice], ["id" => $id]);
    }

    if ($tipo === 'video') {
        $database->update("video", ["indice" => $indice], ["id" => $id]);
    }
}

echo json_encode(["status" => "ok"]);
