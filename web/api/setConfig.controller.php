<?php

require __DIR__ . '/../api/db.php';

// Actualizar RRSS (id = 1)
$database->update('config', [
    'estado' => isset($_POST['rrss']) ? 1 : 0
], [
    'id' => 1
]);

// Actualizar SCROLL (id = 2)
$database->update('config', [
    'estado' => isset($_POST['scroll']) ? 1 : 0
], [
    'id' => 2
]);

// Actualizar TIEMPO (id = 3)
$database->update('config', [
    'estado' => isset($_POST['tiempo']) ? 1 : 0
], [
    'id' => 3
]);

// Recargar la página para reflejar los cambios
header("Location: ../pages/configBascula.php"); // ajusta la ruta si cambia
exit;