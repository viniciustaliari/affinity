<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/programa_package.php';
header('Content-Type: application/json');

$id_programa = (int)($_POST['id_programa'] ?? 0);
$nuevo_contexto = trim((string)($_POST['contexto'] ?? ''));

if ($id_programa <= 0 || $nuevo_contexto === '') {
    echo json_encode(["status" => "error", "msg" => "Faltan datos"]);
    exit;
}

$programa = $database->get("programas", ["id", "contexto"], ["id" => $id_programa]);
if (!$programa) {
    echo json_encode(["status" => "error", "msg" => "Programa no encontrado"]);
    exit;
}

$contextoAnterior = trim((string)($programa["contexto"] ?? ''));

$database->update("programas", [
    "contexto" => $nuevo_contexto
], [
    "id" => $id_programa
]);

if (function_exists('saveProgramaZipSnapshotToDatabase')) {
    $snapshot = saveProgramaZipSnapshotToDatabase($id_programa);
    if (($snapshot["status"] ?? "") !== "ok") {
        // Evita dejar inconsistente el contexto con un manifest antiguo.
        $database->update("programas", [
            "contexto" => $contextoAnterior
        ], [
            "id" => $id_programa
        ]);

        echo json_encode([
            "status" => "error",
            "msg" => "No se pudo actualizar el manifest del paquete al cambiar el contexto.",
            "details" => (string)($snapshot["error"] ?? "unknown_error")
        ]);
        exit;
    }
}

echo json_encode(["status" => "ok"]);
