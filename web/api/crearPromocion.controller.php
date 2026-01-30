<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/db.php';

// Forzar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/promociones.php?error=method");
    exit;
}

$nombre              = $_POST['nombre'] ?? null;
$fecha_inicio        = $_POST['fecha_inicio'] ?? null;
$fecha_fin           = $_POST['fecha_fin'] ?? null;
$nombre_producto     = $_POST['nombre_producto'] ?? null;
$fre_cliente         = $_POST['frecuencia_cliente'] ?? null;
$fre_no_cliente      = $_POST['frecuencia_no_cliente'] ?? null;
$estado              = isset($_POST['estado']) ? 1 : 0;

if (!$nombre || !$fecha_inicio || !$fecha_fin || !$nombre_producto) {
    header("Location: ../pages/promociones.php?error=faltan_datos");
    exit;
}

/* ============================
   SUBIR IMAGEN
   ============================ */

$nombreImagenFinal = null;

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    $dir = __DIR__ . '/../data/promos/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $nombreImagenFinal = uniqid("promo_", true) . "." . $ext;

    move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $nombreImagenFinal);
}

/* ============================
   GUARDAR EN BASE DE DATOS
   ============================ */

$database->insert("promociones", [
    "nombre"                 => $nombre,
    "fecha_inicio"           => $fecha_inicio,
    "fecha_fin"              => $fecha_fin,
    "nombre_producto"        => $nombre_producto,
    "dir_imagen"             => $nombreImagenFinal,
    "frecuencia_cliente"     => $fre_cliente,
    "frecuencia_no_cliente"  => $fre_no_cliente,
    "estado"                 => $estado
]);

$id = $database->id();

if (!$id) {
    header("Location: ../pages/nuevaPromocion.php?error=bd");
    exit;
}

header("Location: ../pages/nuevaPromocion.php?saved=1");
exit;
