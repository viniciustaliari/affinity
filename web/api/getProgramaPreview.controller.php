<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/programa_payload.php';

header('Content-Type: application/json; charset=utf-8');

$programId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($programId <= 0) {
    http_response_code(400);
    echo json_encode([
        "error" => "Missing or invalid 'id'. Example: /api/getProgramaPreview.controller.php?id=12"
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$payload = buildProgramaPayloadByProgramId($programId);
if (isset($payload["error"])) {
    http_response_code(404);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
