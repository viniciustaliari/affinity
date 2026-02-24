<?php
include_once '../components/head.php';
require_once '../api/db.php';

$ipLocal = trim((string)(getenv('HOST_IP') ?: ''));
if ($ipLocal === '' || !filter_var($ipLocal, FILTER_VALIDATE_IP)) {
    // Fallback: si acceden por IP (ej. http://192.168.x.x:8080), mostramos esa IP.
    $hostHeader = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($hostHeader !== '') {
        $hostOnly = preg_replace('/:\d+$/', '', $hostHeader);
        if (is_string($hostOnly) && filter_var($hostOnly, FILTER_VALIDATE_IP)) {
            $ipLocal = $hostOnly;
        }
    }
}
if ($ipLocal === '' || !filter_var($ipLocal, FILTER_VALIDATE_IP)) {
    $ipLocal = '---';
}

$wsPort = trim((string)(getenv('WS_PORT') ?: '8090'));
if ($wsPort === '') {
    $wsPort = '8090';
}

$estadoBD = $database ? "CONECTADO" : "ERROR";
$dbOk = (bool)$database;
?>

<main class="px-3">
    <div class="relative bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl grid grid-rows-6 grid-cols-7 gap-5 h-[80vh]">

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
                CARACTERISTICAS DEL SERVIDOR
            </h3>
            <div></div>
        </div>

        <div class="col-span-7 row-span-5 grid grid-cols-1 md:grid-cols-2 gap-6 px-10 pb-4 items-center content-center">
            <div class="bg-white/95 border border-white/80 rounded-3xl p-6 min-h-[220px] shadow-xl flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold tracking-[0.12em] text-sm text-blue-600">IP LOCAL</h3>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white flex items-center justify-center shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                            <path d="M8 20H6a2 2 0 0 1-2-2v-2"></path>
                            <path d="M4 8V6a2 2 0 0 1 2-2h2"></path>
                            <path d="M20 16v2a2 2 0 0 1-2 2h-2"></path>
                            <path d="M9 9h6v6H9z"></path>
                        </svg>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="font-extrabold text-4xl leading-tight text-slate-800 break-all">
                        <?= htmlspecialchars($ipLocal) ?>
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 bg-blue-50 text-blue-700 px-3 py-1.5 rounded-full text-sm font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        WS PORT <?= htmlspecialchars($wsPort) ?>
                    </div>
                </div>
            </div>

            <div class="bg-white/95 border border-white/80 rounded-3xl p-6 min-h-[220px] shadow-xl flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold tracking-[0.12em] text-sm text-blue-600">ESTADO BD</h3>
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white flex items-center justify-center shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <ellipse cx="12" cy="5" rx="8" ry="3"></ellipse>
                            <path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"></path>
                            <path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"></path>
                        </svg>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="font-extrabold text-4xl leading-tight <?= $dbOk ? 'text-emerald-600' : 'text-red-600' ?>">
                        <?= htmlspecialchars($estadoBD) ?>
                    </p>
                    <div class="mt-4 inline-flex items-center gap-2 <?= $dbOk ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?> px-3 py-1.5 rounded-full text-sm font-semibold">
                        <span class="w-2 h-2 rounded-full <?= $dbOk ? 'bg-emerald-500' : 'bg-red-500' ?>"></span>
                        <?= $dbOk ? 'Conexion operativa' : 'Conexion no disponible' ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
