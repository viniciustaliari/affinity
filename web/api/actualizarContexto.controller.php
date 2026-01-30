<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id_programa = $_POST['id_programa'] ?? null;
$nuevo_contexto = $_POST['contexto'] ?? null;

if (!$id_programa || !$nuevo_contexto) {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$database->update("programas", [
    "contexto" => $nuevo_contexto
], [
    "id" => $id_programa
]);

echo json_encode(["status" => "ok"]);
