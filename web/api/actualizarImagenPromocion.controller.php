<?php
require __DIR__ . '/db.php';
header("Content-Type: application/json");

$id = $_POST['id'] ?? null;

if(!$id){
    echo json_encode(["status"=>"error","msg"=>"ID faltante"]);
    exit;
}

$promo = $database->get("promociones", "*", ["id"=>$id]);
if(!$promo){
    echo json_encode(["status"=>"error","msg"=>"Promoción no encontrada"]);
    exit;
}

if(!isset($_FILES["imagen"])){
    echo json_encode(["status"=>"error","msg"=>"Imagen no recibida"]);
    exit;
}

$file = $_FILES["imagen"];
if($file["error"] !== UPLOAD_ERR_OK){
    echo json_encode(["status"=>"error","msg"=>"Error al subir archivo"]);
    exit;
}

// Eliminar imagen anterior
$rutaAnterior = __DIR__ . '/../data/promos/' . $promo['dir_imagen'];
if(file_exists($rutaAnterior)) unlink($rutaAnterior);

// Guardar nueva
$ext = pathinfo($file["name"], PATHINFO_EXTENSION);
$nombreNuevo = uniqid("promo_", true) . "." . $ext;
move_uploaded_file($file["tmp_name"], __DIR__ . '/../data/promos/' . $nombreNuevo);

// Actualizar BDD
$database->update("promociones", [
    "dir_imagen" => $nombreNuevo
], ["id"=>$id]);

echo json_encode(["status"=>"ok"]);
