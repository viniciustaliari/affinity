<?php 
include_once '../components/head.php'
?>

<main class="px-3">

  <?php require __DIR__ . '/../api/getConfig.controller.php'; ?>
  <?php
    $zonaLibreColor = '#334155';
    $tiempoOn = !empty($config[3]);
    $contPersOn = !empty($config[2]);
    $rssOn = !empty($config[1]);

    $tiempoPlacementStyle = 'grid-row: 1;';
    $contPersPlacementStyle = 'grid-row: 2;';
    $rssPlacementStyle = 'grid-row: 3;';

    // En las dos franjas inferiores:
    // - Si solo una esta activa, se posiciona en la franja inferior (fila 3).
    // - La otra franja queda visible en gris como zona libre.
    if ($contPersOn && !$rssOn) {
      $contPersPlacementStyle = 'grid-row: 3;';
      $rssPlacementStyle = 'grid-row: 2;';
    }

    if (($contPersOn xor $rssOn)) {
      // Si solo una zona inferior es visible, TIEMPO se alarga hasta la fila 2.
      $tiempoPlacementStyle = 'grid-row: 1 / span 2;';
    } elseif (!$contPersOn && !$rssOn) {
      // Si ninguna zona inferior es visible, TIEMPO llega al borde inferior.
      $tiempoPlacementStyle = 'grid-row: 1 / span 3;';
    }
  ?>

  <form method="POST" action="../api/setConfig.controller.php" id="configForm"
        class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl grid grid-rows-8 grid-cols-7 gap-5 h-[80vh]">

    <div class="col-span-7 grid grid-cols-3 items-center px-2">
      <div class="flex items-center justify-start">
        <a href="/pages/configuracion.php"
           class="z-20 w-16 h-16 rounded-full border-2 border-white/85 bg-white/25 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-300 transition shadow-lg"
           aria-label="Volver atras"
           title="Volver atras">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"></path>
            <path d="M12 19L5 12L12 5"></path>
          </svg>
        </a>
      </div>
      <h3 class="font-bold text-xl text-white text-center">
        CONFIGURAR BASCULA
      </h3>
      <div></div>
    </div>

    <!-- TIEMPO (id 3) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div style="width: 340px;" class="bg-white py-3 rounded-xl text-center font-semibold text-slate-800 uppercase tracking-wide box-border">TIEMPO</div>

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

    <!-- CONTENIDO PERSONALIZADO (id 2) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div style="width: 340px;" class="bg-white py-3 rounded-xl text-center font-semibold text-slate-800 uppercase tracking-wide box-border">CONT. PERS.</div>

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

    <!-- RSS (id 1) -->
    <div class="col-span-3 row-span-2 col-start-2 flex flex-col gap-6 items-center justify-center font-bold text-xl">
      <div style="width: 340px;" class="bg-white py-3 rounded-xl text-center font-semibold text-slate-800 uppercase tracking-wide box-border">RSS</div>

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

    <!-- Preview -->
    <div class="col-span-3 row-span-7 row-start-2 col-start-5 flex flex-col gap-2 items-center min-h-0 overflow-hidden">
      <h3 class="font-bold text-xl text-white" style="transform: translateY(-55px);">Preview</h3>
      <div class="w-full flex-1 min-h-0 flex items-start justify-center overflow-hidden pt-1">
        <div class="bg-black overflow-hidden"
             style="aspect-ratio: 1080 / 1920; height: min(68vh, 480px); width: auto; max-width: 96%; transform: translateY(-50px); border-radius: 18px; padding: 10px; border: 2px solid #0f172a; box-shadow: 0 12px 28px rgba(0,0,0,0.45);">
          <div class="bg-gray-700 rounded-[10px] shadow-inner overflow-hidden w-full h-full">
            <div class="w-full h-full grid"
                 style="grid-template-columns: 30% 70%; grid-template-rows: 1fr 12% 12%;">
              <div class="flex items-center justify-center text-white font-semibold tracking-wide"
                   style="<?= $tiempoPlacementStyle ?> writing-mode: vertical-rl; text-orientation: upright; background: <?= $tiempoOn ? 'radial-gradient(circle at 25% 15%, rgba(255,255,255,0.35) 0 12%, transparent 13%), linear-gradient(180deg, #38bdf8 0%, #0ea5e9 45%, #1d4ed8 100%)' : $zonaLibreColor ?>;">
                <span style="<?= $tiempoOn ? '' : 'opacity:0;' ?>">TIEMPO</span>
              </div>

              <div style="background: <?= $zonaLibreColor ?>;"></div>

              <div class="col-span-2 flex items-center justify-center text-white font-semibold tracking-wide"
                   style="<?= $contPersPlacementStyle ?> background: <?= $contPersOn ? 'radial-gradient(circle at 18% 25%, rgba(255,255,255,0.20) 0 12%, transparent 13%), linear-gradient(180deg, #fb923c 0%, #f97316 55%, #c2410c 100%)' : $zonaLibreColor ?>;">
                <span style="<?= $contPersOn ? '' : 'opacity:0;' ?>">CONT. PERS.</span>
              </div>

              <div class="col-span-2 flex items-center justify-center text-white font-bold tracking-wide"
                   style="<?= $rssPlacementStyle ?> background: <?= $rssOn ? 'radial-gradient(circle at 18% 25%, rgba(255,255,255,0.22) 0 12%, transparent 13%), linear-gradient(180deg, #60a5fa 0%, #3b82f6 55%, #1d4ed8 100%)' : $zonaLibreColor ?>;">
                <span style="<?= $rssOn ? '' : 'opacity:0;' ?>">RSS / NOTICIAS</span>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </form>
</main>
