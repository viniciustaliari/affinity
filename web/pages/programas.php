<?php 
include_once '../components/head.php';
require_once '../api/db.php';

// Obtener contextos
$contextos = $database->select("contextos", "*");

// Obtener programas por contexto
$programas = [];
foreach ($contextos as $ctx) {
    $programas[$ctx['id']] = $database->select("programas", "*", [
        "contexto" => $ctx['id']
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
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-5 rounded-2xl flex h-[80vh] gap-10 justify-center items-center">

        <!-- Columna izquierda -->
        <div class="flex flex-col w-3/5 ms-10 overflow-y-auto h-[70vh] hide-scroll justify-center">

            <h2 id="tituloSeleccion" class="text-white text-3xl font-bold mb-5 tracking-wide drop-shadow">
                Selecciona un programa
            </h2>

            <input type="hidden" id="programaSeleccionado">

            <?php foreach ($contextos as $ctx): ?>
            <div class="w-full mb-6">

                <div class="ctx-title text-xl mb-3">
                    <?= strtoupper($ctx['nombre']) ?>
                </div>

                <?php if (!empty($programas[$ctx['id']])): ?>
                    <?php foreach ($programas[$ctx['id']] as $p): ?>
                    <div data-id="<?= $p['id'] ?>" class="programa-card">

                        <!-- Icono -->
                        <img src="../assets/img/play_blue.png" width="45">

                        <!-- Nombre -->
                        <p class="font-bold text-2xl flex-1"><?= htmlspecialchars($p['nombre']) ?></p>

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
        <div class="flex flex-col w-2/5 gap-10">

            <a href="/pages/crearPrograma.php"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow hover:bg-blue-800 hover:text-white transition">
                <img src="../assets/img/plus.png" width="50">
                <p class="font-bold text-2xl">Añadir nuevo programa</p>
            </a>

            <div id="btnEditar"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-blue-800 hover:text-white transition">
                <img src="../assets/img/edit.png" width="50">
                <p class="font-bold text-2xl">Editar programa</p>
            </div>

            <div id="btnEliminar"
               class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-orange-600 hover:text-white transition">
                <img src="../assets/img/trash.png" width="50">
                <p class="font-bold text-2xl">Eliminar programa</p>
            </div>

            <div class="bg-white rounded-2xl p-4 flex items-center gap-4 shadow cursor-pointer hover:bg-blue-800 hover:text-white transition">
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

<script>
// Modal aviso
function mostrarModalAviso(msg) {
    document.getElementById("modal-aviso-text").textContent = msg;
    document.getElementById("modal-aviso").classList.remove("hidden");
}
function cerrarModalAviso() {
    document.getElementById("modal-aviso").classList.add("hidden");
}

// Modal delete
function mostrarModalDelete() {
    document.getElementById("modal-delete").classList.remove("hidden");
}
function cerrarModalDelete() {
    document.getElementById("modal-delete").classList.add("hidden");
}

// Selección del programa
document.querySelectorAll(".programa-card").forEach(item => {
    item.addEventListener("click", () => {

        document.querySelectorAll(".programa-card").forEach(p => {
            p.classList.remove("selected");
            p.querySelector(".check").classList.add("hidden");
        });

        item.classList.add("selected");
        item.querySelector(".check").classList.remove("hidden");

        document.getElementById("programaSeleccionado").value = item.dataset.id;

        document.getElementById("tituloSeleccion").textContent =
            "Programa seleccionado: " + item.querySelector("p").textContent;
    });
});

// Botón editar
document.getElementById("btnEditar").addEventListener("click", () => {
    let id = document.getElementById("programaSeleccionado").value;

    if (!id) {
        mostrarModalAviso("Debes seleccionar un programa antes de editarlo.");
        return;
    }

    window.location.href = "/pages/editarPrograma.php?id=" + id;
});

// BOTÓN ELIMINAR → abre modal de confirmación
document.getElementById("btnEliminar").addEventListener("click", () => {
    let id = document.getElementById("programaSeleccionado").value;

    if (!id) {
        mostrarModalAviso("Selecciona un programa antes de eliminarlo.");
        return;
    }

    mostrarModalDelete();
});

// CONFIRMAR ELIMINAR → AJAX
document.getElementById("btnConfirmDelete").addEventListener("click", () => {
    let id = document.getElementById("programaSeleccionado").value;

    fetch("/api/eliminarPrograma.controller.php?id=" + id, {
        method: "GET"
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === "ok") {
            window.location.reload();
        } else {
            mostrarModalAviso("Error al eliminar el programa.");
        }
    });
});
</script>
