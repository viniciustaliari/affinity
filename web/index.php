<?php 
include_once './components/head.php';
require_once './api/db.php';

// PROGRAMAS ACTIVOS POR CONTEXTO
$contextos = function_exists('getContextosCanonicos')
    ? getContextosCanonicos()
    : [];

$programasActivos = [];

foreach ($contextos as $ctxId => $nombre) {
    $programasActivos[$nombre] = $database->get("programas", "nombre", ["contexto" => $ctxId]);
}

// PROMOCIONES ACTIVAS
$promosActivas = $database->select("promociones", "*", ["estado" => 1]);

// IP del servidor
$ipLocal = getenv('HOST_IP') ?: getHostByName(getHostName());

// Estado BD
$estadoBD = $database ? "CONECTADO" : "ERROR";
?>

<main class="px-3">
<div class="bg-gray-300 p-3 rounded-2xl grid grid-cols-7 gap-4 h-[80vh] text-sm">

    <!-- ░ INFORME DETALLADO (3 columnas) ░ -->
    <div class="col-span-3 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl 
                grid grid-cols-3 gap-3 p-3">

        <h3 class="col-span-3 text-center font-bold text-lg text-white">
            Ver informe detallado
        </h3>

        <?php foreach ($contextos as $ctxId => $ctxNombre): ?>
        <a href="/pages/informeDetallado.php?ctx=<?= (int)$ctxId ?>"
           class="flex items-center justify-center bg-white p-2 rounded-xl font-semibold hover:bg-orange-500 hover:text-white">
            <?= htmlspecialchars(ucwords($ctxNombre)) ?>
        </a>
        <?php endforeach; ?>

        <!-- TOTAL 7 DIAS -->
        <div id="bloqueSemanal"
             class="col-span-3 bg-white rounded-xl flex flex-col items-center justify-center 
                    p-3 gap-1 shadow">
            <p class="font-semibold text-gray-600 text-sm">ÚLTIMOS 7 DÍAS</p>
            <p id="totalSemana" class="font-bold text-3xl text-blue-600">-- €</p>
            <p id="nombreServicio" class="font-semibold text-gray-700 text-sm">---</p>
        </div>

    </div>


    <!-- ░ TOTAL HOY (2 columnas) ░ -->
    <div class="col-span-2 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white">
        <h3 class="font-bold text-xl">TOTAL HOY</h3>

        <p id="totalHoy" class="font-bold text-4xl text-center mt-1">-- €</p>

        <p class="text-center text-sm">
            Ayer: <span id="totalAyer">-- €</span>
        </p>

        <div class="mt-2 bg-white rounded-lg p-2 shadow">
            <canvas id="chartHoyAyer" height="80"></canvas>
        </div>
    </div>


    <div class="col-span-2 flex flex-col gap-4">
        <!-- ░ IP LOCAL (1 columna) ░ -->
        <div class="col-span-1 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white flex flex-col justify-center">
            <h3 class="font-bold text-lg">IP LOCAL</h3>
            <p class="font-bold text-2xl mt-1"><?= $ipLocal ?></p>
        </div>


        <!-- ░ ESTADO BD (1 columna) ░ -->
        <div class="col-span-1 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white flex flex-col justify-center">
            <h3 class="font-bold text-lg">ESTADO BD</h3>
            <p class="font-bold text-2xl mt-1"><?= $estadoBD ?></p>
        </div>

        <div class="col-span-1 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white flex flex-col justify-center">
            <h3 class="font-bold text-lg">ESTADO WS</h3>
            <p id="wsUiStatus" class="font-bold text-xl mt-1">DESCONECTADO</p>
            <p class="text-sm mt-2">Clientes: <span id="wsClientCount">0</span></p>
            <p class="text-sm">IP: <span id="wsLastClientIp">---</span></p>
            <p class="text-sm">Puerto: <span id="wsLastClientPort">---</span></p>
            <p class="text-sm">Ultimo: <span id="wsLastClient">---</span></p>
        </div>
    </div>

    


    <!-- ░ PROGRAMAS ACTIVOS (4 columnas) ░ -->
    <div class="col-span-4 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl p-3 text-white">
        <h3 class="text-center font-bold text-xl">Programas activos</h3>

        <div class="mt-2 space-y-1 ps-6 text-sm">
            <?php foreach ($programasActivos as $nombre => $prog): ?>
                <p class="font-semibold text-lg">
                    <?= $nombre ?>:
                    <span class="opacity-90"><?= $prog ? htmlspecialchars($prog) : "Sin programa" ?></span>
                </p>
            <?php endforeach; ?>
        </div>
    </div>


    <!-- ░ PROMOCIONES (3 columnas) ░ -->
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
                <p class="font-bold text-2xl pt-1">Ninguna promoción activa</p>
            <?php endif; ?>
        </div>
    </div>

</div>

</main>


<!-- ================================ -->
<!--        SCRIPTS DINÁMICOS         -->
<!-- ================================ -->

<script>
// ==========================
// ROTACIÓN SEMANAL (SERVICIOS)
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

    document.getElementById("totalSemana").innerText = item.total.toFixed(2) + "€";
    document.getElementById("nombreServicio").innerText = item.servicio;

    indexServicio = (indexServicio + 1) % datos.length;
}

// iniciar rotación
setInterval(mostrarServicio, 4000);
cargarDatos();
</script>


<!-- ========================== -->
<!--    CHART.js + COMPARACIÓN  -->
<!-- ========================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chartHoyAyer = null;

function cargarComparacionHoyAyer() {
    fetch('/api/getComparacionHoyAyer.controller.php')
        .then(r => r.json())
        .then(d => {

            console.log("DATOS API:", d); // esto ya lo viste

            // Convertimos SIEMPRE a número
            const hoy  = Number(d.hoy)  || 0;
            const ayer = Number(d.ayer) || 0;

            document.getElementById("totalHoy").innerText  = hoy.toFixed(2) + " €";
            document.getElementById("totalAyer").innerText = ayer.toFixed(2) + " €";

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
                label: "€ generados",
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

function setWsUiStatus(text) {
    const el = document.getElementById("wsUiStatus");
    if (el) el.textContent = text;
}

function renderWsClients() {
    const countEl = document.getElementById("wsClientCount");
    const ipEl = document.getElementById("wsLastClientIp");
    const portEl = document.getElementById("wsLastClientPort");
    const lastEl = document.getElementById("wsLastClient");

    if (countEl) countEl.textContent = String(wsClients.size);

    if (!lastEl || !ipEl || !portEl) return;
    if (wsClients.size === 0) {
        ipEl.textContent = "---";
        portEl.textContent = "---";
        lastEl.textContent = "---";
        return;
    }

    const ordered = Array.from(wsClients.values()).sort((a, b) => {
        return String(a.connectedAt || "").localeCompare(String(b.connectedAt || ""));
    });
    const last = ordered[ordered.length - 1];
    const ip = last.ip || "unknown";
    const port = last.port !== null && last.port !== undefined ? String(last.port) : "?";
    const rawAddress = last.rawAddress ? String(last.rawAddress) : "";

    ipEl.textContent = ip;
    portEl.textContent = port;
    lastEl.textContent = rawAddress !== "" ? rawAddress : `${ip}:${port}`;
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

    setWsUiStatus("CONECTANDO...");
    wsUi = new WebSocket(url);

    wsUi.onopen = () => {
        setWsUiStatus("CONECTADO");
        wsUi.send(JSON.stringify({ type: "get_clients" }));
    };

    wsUi.onmessage = (event) => {
        onWsMessage(event.data);
    };

    wsUi.onerror = () => {
        setWsUiStatus("ERROR");
    };

    wsUi.onclose = () => {
        setWsUiStatus("DESCONECTADO");
        setTimeout(connectWsUi, 3000);
    };
}

connectWsUi();
</script>
