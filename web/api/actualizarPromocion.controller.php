<?php
require __DIR__ . '/db.php';
header("Content-Type: application/json");

$id = $_POST['id'] ?? null;

if(!$id){
    echo json_encode(["status"=>"error","msg"=>"ID faltante"]);
    exit;
}

$update = [
    "nombre" => $_POST["nombre"],
    "nombre_producto" => $_POST["producto"],
    "fecha_inicio" => $_POST["inicio"],
    "fecha_fin" => $_POST["fin"],
    "frecuencia_cliente" => $_POST["cliente"],
    "frecuencia_no_cliente" => $_POST["no_cliente"],
    "estado" => $_POST["estado"]
];

$database->update("promociones", $update, ["id"=>$id]);

echo json_encode(["status"=>"ok"]);
