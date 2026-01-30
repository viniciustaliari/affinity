<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id = $_POST['id_programa'] ?? null;
$texto = $_POST['texto'] ?? null;

if (!$id || $texto === null) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$database->update("programas", [
    "texto" => $texto
], [
    "id" => $id
]);

echo json_encode(["status" => "ok"]);
