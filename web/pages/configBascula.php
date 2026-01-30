<?php 
include_once '../components/head.php'
?>

<main class="px-3">

  <?php require __DIR__ . '/../api/getConfig.controller.php'; ?>

  <form method="POST" action="../api/setConfig.controller.php" id="configForm"
        class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl grid grid-rows-8 grid-cols-7 gap-5 h-[80vh]">

    <h3 class="col-span-7 flex items-center justify-center font-bold text-xl text-white">
      Configurar Báscula
    </h3>

    <!-- RRSS (id 1) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div class="bg-white px-20 py-3 rounded-xl">RRSS</div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox"
               name="rrss"
               class="sr-only peer"
               onchange="document.getElementById('configForm').submit()"
               <?= $config[1] ? 'checked' : '' ?>>
        <div class="w-16 h-7 bg-gray-300 rounded-full transition-colors duration-300 peer-checked:bg-green-400"></div>
        <div class="absolute left-0.5 top-0.5 h-6 w-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-8"></div>
      </label>
    </div>

    <!-- SCROLL (id 2) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div class="bg-white px-20 py-3 rounded-xl">SCROLL</div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox"
               name="scroll"
               class="sr-only peer"
               onchange="document.getElementById('configForm').submit()"
               <?= $config[2] ? 'checked' : '' ?>>
        <div class="w-16 h-7 bg-gray-300 rounded-full transition-colors duration-300 peer-checked:bg-green-400"></div>
        <div class="absolute left-0.5 top-0.5 h-6 w-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-8"></div>
      </label>
    </div>

    <!-- TIEMPO (id 3) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div class="bg-white px-20 py-3 rounded-xl">TIEMPO</div>

      <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox"
               name="tiempo"
               class="sr-only peer"
               onchange="document.getElementById('configForm').submit()"
               <?= $config[3] ? 'checked' : '' ?>>
        <div class="w-16 h-7 bg-gray-300 rounded-full transition-colors duration-300 peer-checked:bg-green-400"></div>
        <div class="absolute left-0.5 top-0.5 h-6 w-6 bg-white rounded-full shadow transition-transform duration-300 peer-checked:translate-x-8"></div>
      </label>
    </div>

    <!-- Preview -->
    <div class="col-span-2 row-span-6 row-start-2 col-start-5 flex flex-col gap-6 items-center">
      <h3 class="font-bold text-xl text-white">Preview</h3>
      <div class="bg-gray-700 h-full w-3/4"></div>
    </div>

  </form>
</main>