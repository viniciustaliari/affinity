<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

// Si no existe, lo creamos vacío
if (!$database->has("ticket", ["id" => 1])) {
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

$ticket = $database->get("ticket", [
    "cl_1", "cl_2", "cl_3", "cl_4", "cl_5",
    "pl_1", "pl_2"
], ["id" => 1]);

echo json_encode([
    "cabecera" => [
        $ticket["cl_1"],
        $ticket["cl_2"],
        $ticket["cl_3"],
        $ticket["cl_4"],
        $ticket["cl_5"]
    ],
    "pie" => [
        $ticket["pl_1"],
        $ticket["pl_2"]
    ]
]);
