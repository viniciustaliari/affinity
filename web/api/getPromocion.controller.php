<?php
require __DIR__ . '/db.php';
header("Content-Type: application/json");

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

$promo = $database->get("promociones", "*", ["id" => $id]);

echo json_encode($promo);
