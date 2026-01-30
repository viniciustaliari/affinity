<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$cabecera = $_POST['cabecera'] ?? [];
$pie = $_POST['pie'] ?? [];

// Siempre garantizar índices
$cabecera = array_pad($cabecera, 5, "");
$pie = array_pad($pie, 2, "");

// 1) Comprobar si existe id = 1
$existe = $database->has("ticket", ["id" => 1]);

// 2) Si no existe, crearlo
if (!$existe) {
    $database->insert("ticket", [
        "id" => 1,
        "nombre" => "TICKET PRINCIPAL",
        "cl_1" => "",
        "cl_2" => "",
        "cl_3" => "",
        "cl_4" => "",
        "cl_5" => "",
        "pl_1" => "",
        "pl_2" => ""
    ]);
}

// 3) Actualizar la fila
$database->update("ticket", [
    "cl_1" => $cabecera[0],
    "cl_2" => $cabecera[1],
    "cl_3" => $cabecera[2],
    "cl_4" => $cabecera[3],
    "cl_5" => $cabecera[4],
    "pl_1" => $pie[0],
    "pl_2" => $pie[1]
], ["id" => 1]);

echo json_encode([
    "status" => "ok",
    "mensaje" => "Ticket actualizado correctamente"
]);
