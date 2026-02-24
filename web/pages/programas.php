<?php 
include_once '../components/head.php';
require_once '../api/db.php';

// Obtener contextos
$contextos = $database->select("contextos", "*", [
    "ORDER" => ["id" => "ASC"]
]);

// Obtener programas por contexto
$programas = [];
foreach ($contextos as $ctx) {
    $programas[$ctx['id']] = $database->select("programas", "*", [
        "contexto" => $ctx['id'],
        "ORDER" => ["id" => "DESC"]
    ]);
}
?>

<style>
/* Tarjeta base */
.programa-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 20px;
    margin: 12px 0;
    display: flex;
    align-items: center;
    gap: 20px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: 0.2s ease;
}

/* Hover azul */
.programa-card:hover {
    background: #e0efff;
}

/* Seleccionado */
.programa-card.selected {
    background: #dbeafe; 
    border-color: #f97316; 
    box-shadow: 0 0 12px rgba(0,0,0,0.15);
}

/* Check naranja */
.check {
    background: white;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    font-size: 18px;
    font-weight: bold;
    color: #f97316;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #f97316;
}
.check.hidden {
    display: none;
}

/* Sección contexto */
.ctx-title {
    background: #1e40af;
    border-radius: 10px;
    padding: 6px;
    color: white;
    text-align: center;
    font-weight: bold;
}

/* Ocultar scrollbars */
.hide-scroll {
    scrollbar-width: none;
}
.hide-scroll::-webkit-scrollbar {
    display: none;
}
</style>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-5 rounded-2xl flex h-[80vh] min-h-0 gap-4 items-start overflow-hidden">

        <!-- Columna izquierda -->
        <div class="flex flex-col flex-1 min-w-0 min-h-0 overflow-y-auto h-full hide-scroll pr-2">

            <h2 id="tituloSeleccion" class="text-white text-3xl font-bold mb-5 tracking-wide drop-shadow">
                Selecciona un programa
            </h2>

            <input type="hidden" id="programaSeleccionado">
            <input type="hidden" id="programaContextoSeleccionado">

            <?php foreach ($contextos as $ctx): ?>
            <div class="w-full mb-6">

                <div class="ctx-title text-xl mb-3">
                    <?= strtoupper($ctx['nombre']) ?>
                </div>

                <?php if (!empty($programas[$ctx['id']])): ?>
                    <?php foreach ($programas[$ctx['id']] as $p): ?>
                    <div data-id="<?= (int)$p['id'] ?>"
                         data-contexto="<?= (int)$p['contexto'] ?>"
                         data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                         class="programa-card">

                        <!-- Icono -->
                        <img src="../assets/img/play_blue.png" width="45">

                        <!-- Nombre -->
                        <p class="font-bold text-2xl flex-1 min-w-0 truncate"><?= htmlspecialchars($p['nombre']) ?></p>

                        <!-- Check -->
                        <div class="check hidden">✔</div>

                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-white text-sm italic">No hay programas.</p>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>

        </div>

        <!-- Columna derecha -->
        <div class="flex flex-col w-[360px] max-w-[42%] shrink-0 min-h-0 h-full justify-between overflow-y-auto hide-scroll pr-1">

            <a href="/pages/crearPrograma.php"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/plus.png" width="50">
                <p class="font-bold text-2xl">Añadir nuevo programa</p>
            </a>

            <div id="btnEditar"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/edit.png" width="50">
                <p class="font-bold text-2xl">Editar programa</p>
            </div>

            <div id="btnEliminar"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/trash.png" width="50">
                <p class="font-bold text-2xl">Eliminar programa</p>
            </div>

            <div id="btnEnviarPrograma"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/send.png" width="50">
                <p class="font-bold text-2xl">Enviar programa</p>
            </div>

            <div id="btnPreviewPrograma"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/play_green.png" width="50">
                <p class="font-bold text-2xl">Preview</p>
            </div>

        </div>

    </div>
</main>

<!-- ========================= -->
<!-- MODAL 1: AVISO SIMPLE     -->
<!-- ========================= -->

<div id="modal-aviso" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-7 w-96 shadow-xl text-center">
        <h2 class="text-2xl font-bold text-blue-700 mb-3">Atención</h2>
        <p class="text-gray-700 mb-6" id="modal-aviso-text">Debes seleccionar un programa primero.</p>
        <button onclick="cerrarModalAviso()" 
                class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition">
            OK
        </button>
    </div>
</div>

<!-- ========================= -->
<!-- MODAL 2: CONFIRMAR DELETE -->
<!-- ========================= -->

<div id="modal-delete" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-7 w-96 shadow-xl text-center">

        <h2 class="text-2xl font-bold text-red-600 mb-3">¿Eliminar programa?</h2>

        <p class="text-gray-700 mb-6">
            Esta acción no se puede deshacer.
        </p>

        <div class="flex justify-center gap-4">
            <button onclick="cerrarModalDelete()"
                    class="bg-gray-300 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                Cancelar
            </button>

            <button id="btnConfirmDelete"
                    class="bg-red-500 text-white px-5 py-2 rounded-lg hover:bg-red-600 transition">
                Eliminar
            </button>
        </div>

    </div>
</div>

<!-- ========================= -->
<!-- MODAL 3: SELECCION CLIENTES -->
<!-- ========================= -->

<div id="modal-clientes" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40 hidden">
    <div class="bg-white rounded-2xl p-6 w-[520px] max-w-[95vw] shadow-xl">
        <h2 class="text-2xl font-bold text-blue-700 mb-2">Seleccionar basculas</h2>
        <p class="text-gray-700 mb-4">Elija a que clientes desea enviar el programa.</p>

        <label class="flex items-center gap-2 mb-3 text-sm text-gray-700">
            <input type="checkbox" id="clientes-select-all" class="w-4 h-4">
            Seleccionar todos
        </label>

        <div id="modal-clientes-list" class="border border-gray-200 rounded-xl p-3 h-64 overflow-y-auto bg-gray-50 text-sm">
            <p class="text-gray-500 italic">Sin clientes conectados.</p>
        </div>

        <div class="flex justify-end gap-3 mt-5">
            <button id="btnCancelarEnviarClientes"
                    class="bg-gray-300 px-5 py-2 rounded-lg hover:bg-gray-400 transition">
                Cancelar
            </button>
            <button id="btnConfirmarEnviarClientes"
                    class="bg-orange-500 text-white px-5 py-2 rounded-lg hover:bg-orange-600 transition">
                Enviar
            </button>
        </div>
    </div>
</div>

<script>
function mostrarModalAviso(msg) {
    document.getElementById("modal-aviso-text").textContent = msg;
    document.getElementById("modal-aviso").classList.remove("hidden");
}

function cerrarModalAviso() {
    document.getElementById("modal-aviso").classList.add("hidden");
}

function mostrarModalDelete() {
    document.getElementById("modal-delete").classList.remove("hidden");
}

function cerrarModalDelete() {
    document.getElementById("modal-delete").classList.add("hidden");
}

function mostrarModalClientes() {
    renderizarClientesEnModal();
    document.getElementById("modal-clientes").classList.remove("hidden");
}

function cerrarModalClientes() {
    document.getElementById("modal-clientes").classList.add("hidden");
}

document.querySelectorAll(".programa-card").forEach(item => {
    item.addEventListener("click", () => {
        document.querySelectorAll(".programa-card").forEach(p => {
            p.classList.remove("selected");
            p.querySelector(".check").classList.add("hidden");
        });

        item.classList.add("selected");
        item.querySelector(".check").classList.remove("hidden");

        document.getElementById("programaSeleccionado").value = item.dataset.id;
        document.getElementById("programaContextoSeleccionado").value = item.dataset.contexto || "";
        document.getElementById("tituloSeleccion").textContent =
            "Programa seleccionado: " + item.querySelector("p").textContent;
    });
});

document.getElementById("btnEditar").addEventListener("click", () => {
    const id = document.getElementById("programaSeleccionado").value;
    if (!id) {
        mostrarModalAviso("Debes seleccionar un programa antes de editarlo.");
        return;
    }
    window.location.href = "/pages/editarPrograma.php?id=" + id;
});

document.getElementById("btnEliminar").addEventListener("click", () => {
    const id = document.getElementById("programaSeleccionado").value;
    if (!id) {
        mostrarModalAviso("Selecciona un programa antes de eliminarlo.");
        return;
    }
    mostrarModalDelete();
});

document.getElementById("btnConfirmDelete").addEventListener("click", () => {
    const id = document.getElementById("programaSeleccionado").value;
    fetch("/api/eliminarPrograma.controller.php?id=" + id, { method: "GET" })
        .then(r => r.json())
        .then(data => {
            if (data.status === "ok") {
                window.location.reload();
            } else {
                mostrarModalAviso("Error al eliminar el programa.");
            }
        });
});

let wsProgramasUI = null;
let wsProgramasReady = false;
let programaPendienteEnvio = null;
let wsClientsById = {};

function limpiarClientesWs() {
    wsClientsById = {};
}

function normalizarCliente(raw) {
    if (!raw || typeof raw !== "object") return null;
    const id = Number(raw.connectionId || 0);
    if (!Number.isFinite(id) || id <= 0) return null;

    const ip = String(raw.ip || "unknown");
    const portRaw = raw.port;
    const hasPort = portRaw !== null && portRaw !== undefined && String(portRaw) !== "";
    const port = hasPort ? String(portRaw) : "";
    const endpoint = hasPort ? `${ip}:${port}` : ip;

    return { connectionId: id, ip, port, endpoint };
}

function actualizarClienteWs(rawClient) {
    const client = normalizarCliente(rawClient);
    if (!client) return;
    wsClientsById[client.connectionId] = client;
    if (!document.getElementById("modal-clientes").classList.contains("hidden")) {
        renderizarClientesEnModal();
    }
}

function eliminarClienteWs(rawClient) {
    const client = normalizarCliente(rawClient);
    if (!client) return;
    delete wsClientsById[client.connectionId];
    if (!document.getElementById("modal-clientes").classList.contains("hidden")) {
        renderizarClientesEnModal();
    }
}

function reemplazarClientesWs(rawClients) {
    limpiarClientesWs();
    if (Array.isArray(rawClients)) {
        rawClients.forEach(actualizarClienteWs);
    }
}

function obtenerClientesWsOrdenados() {
    return Object.values(wsClientsById).sort((a, b) => a.connectionId - b.connectionId);
}

function actualizarEstadoSelectAll() {
    const selectAll = document.getElementById("clientes-select-all");
    const checks = Array.from(document.querySelectorAll(".cliente-target-check"));

    if (!checks.length) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        selectAll.disabled = true;
        return;
    }

    const selectedCount = checks.filter(chk => chk.checked).length;
    selectAll.disabled = false;
    selectAll.checked = selectedCount === checks.length;
    selectAll.indeterminate = selectedCount > 0 && selectedCount < checks.length;
}

function renderizarClientesEnModal() {
    const list = document.getElementById("modal-clientes-list");
    const btnConfirm = document.getElementById("btnConfirmarEnviarClientes");
    const clients = obtenerClientesWsOrdenados();

    list.innerHTML = "";
    if (!clients.length) {
        list.innerHTML = '<p class="text-gray-500 italic">Sin clientes conectados.</p>';
        btnConfirm.disabled = true;
        btnConfirm.classList.add("opacity-50", "cursor-not-allowed");
        actualizarEstadoSelectAll();
        return;
    }

    clients.forEach(client => {
        const row = document.createElement("label");
        row.className = "flex items-center gap-3 bg-white border border-gray-200 rounded-lg px-3 py-2 mb-2";
        row.innerHTML = `
            <input type="checkbox" class="cliente-target-check w-4 h-4" data-client-id="${client.connectionId}" checked>
            <div class="flex flex-col">
                <span class="font-semibold text-gray-800">${client.endpoint}</span>
                <span class="text-xs text-gray-500">ID conexion: ${client.connectionId}</span>
            </div>
        `;
        list.appendChild(row);
    });

    list.querySelectorAll(".cliente-target-check").forEach(chk => {
        chk.addEventListener("change", actualizarEstadoSelectAll);
    });

    btnConfirm.disabled = false;
    btnConfirm.classList.remove("opacity-50", "cursor-not-allowed");
    actualizarEstadoSelectAll();
}

function obtenerClientesSeleccionadosDesdeModal() {
    return Array.from(document.querySelectorAll(".cliente-target-check:checked"))
        .map(chk => Number(chk.dataset.clientId || 0))
        .filter(id => Number.isFinite(id) && id > 0);
}

function mostrarResultadoEnvio(msg) {
    if (msg.status !== "ok") {
        mostrarModalAviso(msg.error || "No se pudo enviar el programa.");
        return;
    }

    const nombre = msg.program_name ? ` (${msg.program_name})` : "";
    const sizeKb = msg.zip_size_bytes ? (msg.zip_size_bytes / 1024).toFixed(1) : "0.0";
    const missingCount = Array.isArray(msg.missing_files) ? msg.missing_files.length : 0;
    const receivers = Number(msg.receivers_connected || 0);
    const selected = Number(msg.clients_selected || 0);
    const sent = Number(msg.clients_sent || 0);
    const missingClients = Array.isArray(msg.clients_missing) ? msg.clients_missing : [];
    const missingClientsText = missingClients.length
        ? ` No conectados: ${missingClients.join(", ")}.`
        : "";

    mostrarModalAviso(
        `Programa enviado${nombre}. ZIP: ${msg.zip_name || "program.zip"} (${sizeKb} KB). Receptores conectados: ${receivers}. Seleccionados: ${selected}. Clientes notificados: ${sent}. Archivos faltantes: ${missingCount}.${missingClientsText}`
    );
}

function conectarWsProgramas() {
    const proto = window.location.protocol === "https:" ? "wss" : "ws";
    const url = `${proto}://${window.location.hostname}:8090?role=ui&source=programas`;

    wsProgramasUI = new WebSocket(url);

    wsProgramasUI.onopen = () => {
        wsProgramasReady = true;
        wsProgramasUI.send(JSON.stringify({ type: "get_clients" }));
    };

    wsProgramasUI.onclose = () => {
        wsProgramasReady = false;
        limpiarClientesWs();
        setTimeout(conectarWsProgramas, 3000);
    };

    wsProgramasUI.onerror = () => {
        wsProgramasReady = false;
    };

    wsProgramasUI.onmessage = (event) => {
        let msg = null;
        try {
            msg = JSON.parse(event.data);
        } catch (e) {
            return;
        }

        if (!msg || !msg.type) return;

        if (msg.type === "clients_snapshot") {
            reemplazarClientesWs(msg.clients || []);
            return;
        }
        if (msg.type === "client_connected" || msg.type === "client_updated") {
            actualizarClienteWs(msg.client || null);
            return;
        }
        if (msg.type === "client_disconnected") {
            eliminarClienteWs(msg.client || null);
            return;
        }
        if (msg.type === "push_program_result") {
            mostrarResultadoEnvio(msg);
        }
    };
}

document.getElementById("btnEnviarPrograma").addEventListener("click", () => {
    const id = document.getElementById("programaSeleccionado").value;
    if (!id) {
        mostrarModalAviso("Debes seleccionar un programa antes de enviarlo.");
        return;
    }

    if (!wsProgramasUI || !wsProgramasReady || wsProgramasUI.readyState !== WebSocket.OPEN) {
        mostrarModalAviso("La conexion WebSocket de control no esta disponible.");
        return;
    }

    programaPendienteEnvio = Number(id);
    wsProgramasUI.send(JSON.stringify({ type: "get_clients" }));
    mostrarModalClientes();
});

document.getElementById("btnCancelarEnviarClientes").addEventListener("click", () => {
    programaPendienteEnvio = null;
    cerrarModalClientes();
});

document.getElementById("btnConfirmarEnviarClientes").addEventListener("click", () => {
    if (!programaPendienteEnvio) {
        cerrarModalClientes();
        mostrarModalAviso("Debes seleccionar un programa antes de enviarlo.");
        return;
    }

    if (!wsProgramasUI || !wsProgramasReady || wsProgramasUI.readyState !== WebSocket.OPEN) {
        cerrarModalClientes();
        mostrarModalAviso("La conexion WebSocket de control no esta disponible.");
        return;
    }

    const clientIds = obtenerClientesSeleccionadosDesdeModal();
    if (!clientIds.length) {
        mostrarModalAviso("Debe seleccionar al menos un cliente.");
        return;
    }

    wsProgramasUI.send(JSON.stringify({
        type: "push_program",
        program_id: Number(programaPendienteEnvio),
        client_ids: clientIds
    }));

    programaPendienteEnvio = null;
    cerrarModalClientes();
});

document.getElementById("clientes-select-all").addEventListener("change", (event) => {
    const checked = !!event.target.checked;
    document.querySelectorAll(".cliente-target-check").forEach(chk => {
        chk.checked = checked;
    });
    actualizarEstadoSelectAll();
});

document.getElementById("btnPreviewPrograma").addEventListener("click", () => {
    const id = document.getElementById("programaSeleccionado").value;
    if (!id) {
        mostrarModalAviso("Debes seleccionar un programa antes de hacer preview.");
        return;
    }
    window.location.href = "/pages/previewPrograma.php?id=" + encodeURIComponent(id);
});

conectarWsProgramas();
</script>
