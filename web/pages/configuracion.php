<?php
include_once '../components/head.php';
?>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl h-[80vh] flex flex-col">
        <h3 class="flex items-center justify-center font-bold text-xl text-white">
            CONFIGURACION
        </h3>

        <div class="flex-1 flex flex-col gap-6 items-center justify-center font-bold text-xl">

            <a href="/pages/configBascula.php" class="text-blue-500 bg-white p-3 rounded-lg font-bold flex items-center justify-center hover:bg-blue-600 hover:text-white border-2 border-orange-300 w-1/2 text-center">
                CONFIGURACION DE BASCULA
            </a>

            <a href="/pages/editarPrecios.php" class="text-blue-500 bg-white p-3 rounded-lg font-bold flex items-center justify-center hover:bg-blue-600 hover:text-white border-2 border-orange-300 w-1/2 text-center">
                PRECIFICACION
            </a>

            <a href="/pages/configServidor.php" class="text-blue-500 bg-white p-3 rounded-lg font-bold flex items-center justify-center hover:bg-blue-600 hover:text-white border-2 border-orange-300 w-1/2 text-center">
                CARACTERISTICAS DEL SERVIDOR
            </a>

        </div>
    </div>
</main>
