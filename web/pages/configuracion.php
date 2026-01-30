<?php 
include_once '../components/head.php'
?>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl grid grid-rows-6 grid-cols-7 gap-5 h-[80vh]">

        <h3 class="col-span-7 flex items-center justify-center font-bold text-xl text-white">
            CONFIGURACIÓN
        </h3>

        <div class="col-span-7 row-span-4 flex flex-col gap-6 items-center justify-center font-bold text-xl">

            <a href="/pages/configBascula.php" class="text-blue-500 bg-white p-3 rounded-lg font-bold flex items-center justify-center hover:bg-blue-600 hover:text-white border-2 border-orange-300 w-1/2 text-center">
                CONFIGURACIÓN DE BÁSCULA
            </a>

            <a href="/pages/editarPrecios.php" class="text-blue-500 bg-white p-3 rounded-lg font-bold flex items-center justify-center hover:bg-blue-600 hover:text-white border-2 border-orange-300 w-1/2 text-center">
                PRECIFICACIÓN
            </a>

        </div>

    </div>
</main>
