<?php

require __DIR__ . '/../api/db.php';

// Actualizar PESO/ALTURA (id = 1)
if (isset($_POST['peso'])) {
    $database->update('servicios', [
        'precio' => $_POST['peso']
    ], ['id' => 1]);
}

// BLOOD PRESSURE (id = 2)
if (isset($_POST['bp'])) {
    $database->update('servicios', [
        'precio' => $_POST['bp']
    ], ['id' => 2]);
}

// OXI (id = 3)
if (isset($_POST['oxi'])) {
    $database->update('servicios', [
        'precio' => $_POST['oxi']
    ], ['id' => 3]);
}

// IMG (id = 4)
if (isset($_POST['img'])) {
    $database->update('servicios', [
        'precio' => $_POST['img']
    ], ['id' => 4]);
}

// TODO (id = 5)
if (isset($_POST['todo'])) {
    $database->update('servicios', [
        'precio' => $_POST['todo']
    ], ['id' => 5]);
}

// Recargar página
header("Location: ../pages/editarPrecios.php?saved=1");
exit;
