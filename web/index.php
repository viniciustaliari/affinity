<?php 
include_once './components/head.php';
require_once './api/db.php';

// PROGRAMAS ACTIVOS POR CONTEXTO
$contextos = function_exists('getContextosCanonicos')
    ? getContextosCanonicos()
    : [];
$contextosServicio = function_exists('getContextosServicioCanonicos')
    ? getContextosServicioCanonicos()
    : $contextos;

$programasActivos = [];
$rowsProgramasEnviados = $database->select("programas_enviados_activos", [
    "contexto",
    "program_name",
    "sent_at"
], [
    "ORDER" => ["sent_at" => "DESC"]
]);

foreach ($rowsProgramasEnviados as $row) {
    $contextoRaw = trim((string)($row["contexto"] ?? ""));
    if ($contextoRaw === "") {
        continue;
    }

    if (ctype_digit($contextoRaw)) {
        $ctxId = intval($contextoRaw);
        $ctxNombre = $contextos[$ctxId] ?? ("Contexto " . $contextoRaw);
    } else {
        $ctxNombre = $contextoRaw;
    }

    $programasActivos[] = [
        "contexto" => $ctxNombre,
        "programa" => (string)($row["program_name"] ?? "")
    ];
}

// PROMOCIONES ACTIVAS
$promosActivas = $database->select("promociones", "*", ["estado" => 1]);

?>

<main class="px-3">
<div class="bg-gray-300 p-3 rounded-2xl grid grid-cols-7 gap-4 h-[80vh] text-sm">

    <!-- INFORME DETALLADO (3 columnas) -->
    <div class="col-span-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl 
                grid grid-cols-3 gap-3 p-3">

        <h3 class="col-span-3 text-center font-bold text-lg text-white">
            VER INFORME DETALLADO
        </h3>

        <?php foreach ($contextosServicio as $ctxId => $ctxNombre): ?>
        <a href="/pages/informeDetallado.php?ctx=<?= (int)$ctxId ?>"
           class="flex items-center justify-center bg-white p-2 rounded-xl font-semibold hover:bg-orange-500 hover:text-white">
            <?= htmlspecialchars(ucwords($ctxNombre)) ?>
        </a>
        <?php endforeach; ?>

        <!-- TOTAL 7 DIAS -->
        <div id="bloqueSemanal"
             class="col-span-3 bg-white rounded-xl flex flex-col items-center justify-center 
                    p-3 gap-1 shadow">
            <p class="font-semibold text-gray-600 text-sm">&Uacute;LTIMOS 7 D&Iacute;AS</p>
            <p id="totalSemana" class="font-bold text-3xl text-blue-600">-- &euro;</p>
            <p id="nombreServicio" class="font-semibold text-gray-700 text-sm">---</p>
        </div>

    </div>


    <!-- TOTAL HOY (2 columnas) -->
    <div class="col-span-2 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white">
        <h3 class="font-bold text-xl">TOTAL HOY</h3>

        <p id="totalHoy" class="font-bold text-4xl text-center mt-1">-- &euro;</p>

        <p class="text-center text-sm">
            Ayer: <span id="totalAyer">-- &euro;</span>
        </p>

        <div class="mt-2 bg-white rounded-lg p-2 shadow">
            <canvas id="chartHoyAyer" height="80"></canvas>
        </div>
    </div>


    <div class="col-span-2">
        <div class="h-full bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white flex flex-col">
            <h3 class="font-bold text-lg">BASCULAS CONECTADAS</h3>
            <div id="wsClientList" class="text-base mt-2 space-y-1 overflow-y-auto pr-1 flex-1 font-semibold">
                <p class="italic opacity-90">Sin clientes</p>
            </div>
        </div>
    </div>

    

    


    <!-- PROGRAMAS ACTIVOS (4 columnas) -->
    <div class="col-span-4 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white">
        <h3 class="text-center font-bold text-xl">PROGRAMAS ACTIVOS</h3>

        <div class="mt-2 space-y-1 ps-6 text-sm">
            <?php if (!empty($programasActivos)): ?>
                <?php foreach ($programasActivos as $item): ?>
                    <p class="font-semibold text-lg">
                        <?= htmlspecialchars(ucwords((string)$item["contexto"])) ?>:
                        <span class="opacity-90"><?= htmlspecialchars((string)$item["programa"]) ?></span>
                    </p>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="font-bold text-lg pt-1">No hay programas activos.</p>
            <?php endif; ?>
        </div>
    </div>


    <!-- PROMOCIONES (3 columnas) -->
    <div class="col-span-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white">
        <h3 class="font-bold text-xl ps-6">PROMOCIONES</h3>

        <div class="mt-2 ps-6 space-y-1 text-sm">
            <?php if (count($promosActivas) > 0): ?>
                <?php foreach ($promosActivas as $promo): ?>
                    <p class="font-bold text-xl">
                        <?= htmlspecialchars($promo['nombre']) ?>
                        <span class="text-sm opacity-80">(<?= htmlspecialchars($promo['nombre_producto']) ?>)</span>
                    </p>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="font-bold text-lg pt-1">Ninguna promoci&oacute;n activa</p>
            <?php endif; ?>
        </div>
    </div>

</div>

</main>


<!-- ================================ -->
<!--        SCRIPTS DINAMICOS         -->
<!-- ================================ -->

<script>
// ==========================
// ROTACION SEMANAL (SERVICIOS)
// ==========================
let datos = [];
let indexServicio = 0;

async function cargarDatos() {
    const r = await fetch("/api/getTotalesSemanales.controller.php");
    const d = await r.json();

    if (d.status === "ok") {
        datos = d.datos;
        mostrarServicio();
    }
}

function mostrarServicio() {
    if (datos.length === 0) return;

    const item = datos[indexServicio];

    document.getElementById("totalSemana").innerText = item.total.toFixed(2) + "\u20AC";
    document.getElementById("nombreServicio").innerText = item.servicio;

    indexServicio = (indexServicio + 1) % datos.length;
}

// iniciar rotacion
setInterval(mostrarServicio, 4000);
cargarDatos();
</script>


<!-- ========================== -->
<!--    CHART.js + COMPARACION  -->
<!-- ========================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chartHoyAyer = null;

function cargarComparacionHoyAyer() {
    fetch('/api/getComparacionHoyAyer.controller.php')
        .then(r => r.json())
        .then(d => {

            console.log("DATOS API:", d); // esto ya lo viste

            // Convertimos SIEMPRE a numero
            const hoy  = Number(d.hoy)  || 0;
            const ayer = Number(d.ayer) || 0;

            document.getElementById("totalHoy").innerText  = hoy.toFixed(2) + " \u20AC";
            document.getElementById("totalAyer").innerText = ayer.toFixed(2) + " \u20AC";

            renderChartHoyAyer(hoy, ayer);
        });
}

function renderChartHoyAyer(hoy, ayer) {
    const ctx = document.getElementById("chartHoyAyer").getContext("2d");

    if (chartHoyAyer) chartHoyAyer.destroy();

    chartHoyAyer = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Ayer", "Hoy"],
            datasets: [{
                label: "\u20AC generados",
                data: [ayer, hoy],
                backgroundColor: ["#ff7f50", "#4CAF50"]
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true },
                x: { beginAtZero: true }
            }
        }
    });
}

cargarComparacionHoyAyer();
setInterval(cargarComparacionHoyAyer, 60000);
</script>

<script>
let wsUi = null;
let wsClients = new Map();

function renderWsClients() {
    const listEl = document.getElementById("wsClientList");
    if (!listEl) return;

    if (wsClients.size === 0) {
        listEl.innerHTML = '<p class="italic opacity-90 font-normal">Sin clientes</p>';
        return;
    }

    const ordered = Array.from(wsClients.values()).sort((a, b) =>
        String(a.connectedAt || "").localeCompare(String(b.connectedAt || ""))
    );

    const rows = ordered.map((client) => {
        const ip = client.ip || "unknown";
        const port = client.port !== null && client.port !== undefined ? String(client.port) : "?";
        return `<p class="text-lg leading-tight">${ip}:${port}</p>`;
    });

    listEl.innerHTML = rows.join("");
}

function applySnapshot(clients) {
    wsClients = new Map();
    if (Array.isArray(clients)) {
        clients.forEach((c) => {
            if (c && c.connectionId !== undefined) {
                wsClients.set(String(c.connectionId), c);
            }
        });
    }
    renderWsClients();
}

function onWsMessage(raw) {
    let msg = null;
    try {
        msg = JSON.parse(raw);
    } catch (e) {
        return;
    }

    if (!msg || !msg.type) return;

    if (msg.type === "clients_snapshot") {
        applySnapshot(msg.clients);
        return;
    }

    if (msg.type === "client_connected" && msg.client) {
        wsClients.set(String(msg.client.connectionId), msg.client);
        renderWsClients();
        return;
    }

    if (msg.type === "client_disconnected" && msg.client) {
        wsClients.delete(String(msg.client.connectionId));
        renderWsClients();
        return;
    }

    if (msg.type === "client_updated" && msg.client) {
        wsClients.set(String(msg.client.connectionId), msg.client);
        renderWsClients();
        return;
    }
}

function connectWsUi() {
    const proto = window.location.protocol === "https:" ? "wss" : "ws";
    const url = `${proto}://${window.location.hostname}:8090?role=ui`;

    wsUi = new WebSocket(url);

    wsUi.onopen = () => {
        wsUi.send(JSON.stringify({ type: "get_clients" }));
    };

    wsUi.onmessage = (event) => {
        onWsMessage(event.data);
    };

    wsUi.onerror = () => {
        wsClients = new Map();
        renderWsClients();
    };

    wsUi.onclose = () => {
        wsClients = new Map();
        renderWsClients();
        setTimeout(connectWsUi, 3000);
    };
}

connectWsUi();
</script>
