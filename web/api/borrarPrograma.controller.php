<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

$id_programa = $_POST['id_programa'] ?? null;

if (!$id_programa) {
    echo json_encode(["status" => "error", "msg" => "ID no recibido"]);
    exit;
}

/* ===================
   1. BORRAR IMÁGENES
=================== */
$imagenes = $database->select("imagenes", "*", [
    "id_programa" => $id_programa
]);

foreach ($imagenes as $img) {
    $ruta = __DIR__ . '/../data/images/' . $img['nombre'];
    if (file_exists($ruta)) unlink($ruta);
}

/* ===================
   2. BORRAR VIDEOS
=================== */
$videos = $database->select("video", "*", [
    "id_programa" => $id_programa
]);

foreach ($videos as $vid) {
    $ruta = __DIR__ . '/../data/videos/' . $vid['nombre'];
    if (file_exists($ruta)) unlink($ruta);
}

/* ===================
   3. BORRAR PROGRAMA
   (CASCADE borra las filas hijas)
=================== */
$database->delete("programas", [
    "id" => $id_programa
]);

echo json_encode([
    "status" => "ok",
    "msg" => "Programa eliminado correctamente"
]);
