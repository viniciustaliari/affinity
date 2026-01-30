<?php 
include_once '../components/head.php';
require_once '../api/db.php';

// Obtener todas las promociones
$promos = $database->select("promociones", "*");
?>

<style>
/* Ocultar scrollbars */
.hide-scroll::-webkit-scrollbar { width: 0px; }
.hide-scroll { scrollbar-width: none; }
</style>

<!-- MODAL BORRAR -->
<div id="modalBorrar" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 justify-center items-center">
    <div class="bg-white p-6 rounded-xl shadow-xl w-96 text-center">
        <h2 class="text-xl font-bold text-red-600 mb-4">¿Eliminar promoción?</h2>
        <p class="text-gray-700 mb-6">Esta acción no se puede deshacer.</p>

        <input type="hidden" id="promoABorrar">

        <div class="flex justify-center gap-4">
            <button onclick="cerrarModal()" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="borrarPromoConfirmado()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Eliminar
            </button>
        </div>
    </div>
</div>

<!-- MODAL PROMO INACTIVA -->
<div id="modalInactiva" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 justify-center items-center">
    <div class="bg-white p-6 rounded-xl shadow-xl w-96 text-center">
        <h2 class="text-2xl font-bold text-red-600 mb-4">Promoción inactiva</h2>
        <p class="text-gray-700 mb-6">No se puede realizar un sorteo porque la promoción no está activa.</p>

        <button onclick="cerrarModalInactiva()"
                class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600">
            Cerrar
        </button>
    </div>
</div>

<!-- MODAL SORTEO -->
<div id="modalSorteo" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 justify-center items-center">
    <div class="bg-white p-6 rounded-xl shadow-xl w-96 text-center">
        <h2 class="text-2xl font-bold text-blue-700 mb-4">Selecciona tipo de usuario</h2>

        <input type="hidden" id="promoSorteoID">

        <div class="flex flex-col gap-4">
            <button onclick="hacerSorteo('cliente')"
                    class="bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">
                Cliente
            </button>

            <button onclick="hacerSorteo('no_cliente')"
                    class="bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600">
                No cliente
            </button>
        </div>

        <button onclick="cerrarModalSorteo()" class="mt-5 text-gray-600 hover:text-gray-800 text-sm">
            Cancelar
        </button>
    </div>
</div>

<!-- MODAL RESULTADO -->
<div id="modalResultado" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 justify-center items-center">
    <div class="bg-white p-6 rounded-xl shadow-xl w-96 text-center">
        <h2 id="resultadoTitulo" class="text-2xl font-bold mb-4"></h2>
        <p id="resultadoTexto" class="text-gray-700 mb-6"></p>

        <button onclick="cerrarModalResultado()"
                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">
            OK
        </button>
    </div>
</div>

<main class="px-3">
<div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-4 rounded-2xl flex h-[80vh] gap-10 justify-center items-center">

    <!-- LISTADO -->
    <div class="flex flex-col items-center bg-white rounded-2xl p-5 gap-5 h-[70vh] w-3/5 hide-scroll overflow-y-auto">

        <h2 class="text-2xl font-bold text-blue-700 mb-3">PROMOCIONES</h2>

        <?php foreach ($promos as $p): ?>
        <div class="w-full flex flex-row justify-between items-center bg-gray-100 p-3 rounded-xl shadow">

            <!-- IZQUIERDA -->
            <div class="flex flex-row items-center gap-4">
                <img src="/data/promos/<?= htmlspecialchars($p['dir_imagen']) ?>"
                     width="60" class="rounded shadow">

                <div>
                    <p class="font-bold text-xl"><?= htmlspecialchars($p['nombre']) ?></p>
                    <p class="text-sm text-gray-600">
                        <?= htmlspecialchars($p['nombre_producto']) ?>
                    </p>

                    <p class="text-xs mt-1 font-bold 
                        <?= $p['estado'] ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $p['estado'] ? 'ACTIVA' : 'INACTIVA' ?>
                    </p>
                </div>
            </div>

            <!-- CENTRO: porcentajes -->
            <div class="flex flex-col items-center">
                <p class="text-xl font-bold text-blue-700">
                    <?= $p['frecuencia_cliente'] ?>%
                </p>
                <p class="text-xs text-gray-500">(Cliente)</p>
            </div>
            <div class="flex flex-col items-center">
                <p class="text-xl font-bold text-orange-600">
                    <?= $p['frecuencia_no_cliente'] ?>%
                </p>
                <p class="text-xs text-gray-500">(No cliente)</p>
            </div>

            <!-- DERECHA -->
            <div class="flex items-center gap-4">

                <!-- SORTEAR -->
                <button onclick="validarSorteo(<?= $p['id'] ?>, <?= $p['estado'] ?>)"
                        class="bg-orange-500 text-white px-3 py-2 rounded-lg hover:bg-orange-600">
                    Sortear
                </button>

                <!-- EDITAR -->
                <a href="/pages/editarPromocion.php?id=<?= $p['id'] ?>">
                    <img src="../assets/img/lapiz.png" width="28" class="cursor-pointer hover:scale-110">
                </a>

                <!-- BORRAR -->
                <img src="../assets/img/trash.png" width="28"
                     class="cursor-pointer hover:scale-110"
                     onclick="abrirModal(<?= $p['id'] ?>)">
            </div>

        </div>
        <?php endforeach; ?>

        <?php if (count($promos) === 0): ?>
        <p class="text-gray-500 font-bold mt-10 text-xl">No hay promociones registradas.</p>
        <?php endif; ?>

    </div>

    <!-- DERECHA -->
    <div class="flex flex-col w-2/5 gap-5 h-[70vh]">

        <a href="/pages/nuevaPromocion.php"
           class="text-blue-500 bg-white p-5 rounded-lg font-bold flex items-center justify-center
                  hover:bg-blue-600 hover:text-white border-2 border-orange-300 text-center">
            NUEVA PROMOCIÓN
        </a>
    </div>

</div>
</main>

<script>
/* ======================= */
/*      BORRAR PROMO       */
/* ======================= */

function abrirModal(id) {
    document.getElementById("promoABorrar").value = id;
    document.getElementById("modalBorrar").classList.remove("hidden");
    document.getElementById("modalBorrar").classList.add("flex");
}

function cerrarModal() {
    document.getElementById("modalBorrar").classList.add("hidden");
    document.getElementById("modalBorrar").classList.remove("flex");
}

function borrarPromoConfirmado() {
    const id = document.getElementById("promoABorrar").value;

    fetch("/api/eliminarPromocion.controller.php?id=" + id)
        .then(r => r.json())
        .then(d => {
            if (d.status === "ok") location.reload();
            else alert("Error al borrar");
        });
}

/* ======================= */
/*   VALIDAR SI ESTÁ ACTIVA */
/* ======================= */

function validarSorteo(idPromo, estado) {
    if (estado == 0) {
        document.getElementById("modalInactiva").classList.remove("hidden");
        document.getElementById("modalInactiva").classList.add("flex");
        return;
    }

    abrirModalSorteo(idPromo);
}

function cerrarModalInactiva() {
    document.getElementById("modalInactiva").classList.add("hidden");
    document.getElementById("modalInactiva").classList.remove("flex");
}

/* ======================= */
/*      SORTEO PROMO       */
/* ======================= */

function abrirModalSorteo(id) {
    document.getElementById("promoSorteoID").value = id;
    document.getElementById("modalSorteo").classList.remove("hidden");
    document.getElementById("modalSorteo").classList.add("flex");
}

function cerrarModalSorteo() {
    document.getElementById("modalSorteo").classList.add("hidden");
    document.getElementById("modalSorteo").classList.remove("flex");
}

function cerrarModalResultado() {
    document.getElementById("modalResultado").classList.add("hidden");
    document.getElementById("modalResultado").classList.remove("flex");
}

function hacerSorteo(tipo) {
    let idPromo = document.getElementById("promoSorteoID").value;

    fetch("/api/getPromocion.controller.php?id=" + idPromo)
        .then(r => r.json())
        .then(promo => {

            let frecuencia = tipo === "cliente"
                ? promo.frecuencia_cliente
                : promo.frecuencia_no_cliente;

            let prob = frecuencia / 100;
            let gana = Math.random() < prob;

            cerrarModalSorteo();

            if (gana) {
                document.getElementById("resultadoTitulo").innerHTML = "🎉 ¡Has ganado!";
                document.getElementById("resultadoTexto").innerHTML =
                    "Felicidades, la promoción se ha aplicado correctamente.";
            } else {
                document.getElementById("resultadoTitulo").innerHTML = "❌ No ha ganado";
                document.getElementById("resultadoTexto").innerHTML =
                    "Sigue intentándolo, la próxima puede ser tuya.";
            }

            document.getElementById("modalResultado").classList.remove("hidden");
            document.getElementById("modalResultado").classList.add("flex");
        });
}
</script>
