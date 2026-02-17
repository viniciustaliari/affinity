<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

/* 1. Obtener imágenes del programa */
$imagenes = $database->select("imagenes", "*", [
    "id_programa" => $id
]);

/* 2. Obtener videos del programa */
$videos = $database->select("video", "*", [
    "id_programa" => $id
]);

/* 3. Borrar archivos del disco */
foreach ($imagenes as $img) {
    $ruta = __DIR__ . '/../data/images/' . $img['nombre'];
    if (file_exists($ruta)) unlink($ruta);
}

foreach ($videos as $vid) {
    $ruta = __DIR__ . '/../data/videos/' . $vid['nombre'];
    if (file_exists($ruta)) unlink($ruta);
}

/* 4. Borrar el programa → esto borra registros asociados por CASCADE */
$database->delete("programa_paquetes", [
    "id_programa" => $id
]);

/* 5. Borrar el programa → esto borra registros asociados por CASCADE */
$database->delete("programas", [
    "id" => $id
]);

echo json_encode(["status" => "ok"]);
