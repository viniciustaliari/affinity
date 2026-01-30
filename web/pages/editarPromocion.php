<?php
include_once '../components/head.php';
require_once '../api/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Falta ID.");

// Obtener datos
$promo = $database->get("promociones", "*", ["id" => $id]);
if (!$promo) die("Promoción no encontrada.");

?>
<style>
.modal-bg { position: fixed; inset:0; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; z-index:1000; }
.modal { background:white; padding:25px; border-radius:16px; width:420px; }
</style>

<main class="px-3">
<div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl flex h-[80vh] gap-10 justify-center items-center">

    <!-- FORMULARIO -->
    <div class="bg-white p-6 rounded-2xl w-3/5 h-[70vh] overflow-y-auto">

        <h2 class="text-3xl font-bold text-blue-700 mb-6">Editar promoción</h2>

        <label class="font-bold text-blue-800">Nombre</label>
        <input id="nombre" type="text" value="<?= htmlspecialchars($promo['nombre']) ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Producto</label>
        <input id="producto" type="text" value="<?= htmlspecialchars($promo['nombre_producto']) ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Fecha inicio</label>
        <input id="inicio" type="date" value="<?= $promo['fecha_inicio'] ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Fecha fin</label>
        <input id="fin" type="date" value="<?= $promo['fecha_fin'] ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Frecuencia cliente %</label>
        <input id="cliente" type="number" step="0.1" value="<?= $promo['frecuencia_cliente'] ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Frecuencia NO cliente %</label>
        <input id="no_cliente" type="number" step="0.1" value="<?= $promo['frecuencia_no_cliente'] ?>" class="w-full p-3 rounded-lg border mb-4">

        <label class="font-bold text-blue-800">Estado</label>
        <select id="estado" class="w-full p-3 rounded-lg border mb-4">
            <option value="1" <?= $promo['estado'] == 1 ? 'selected' : '' ?>>Activa</option>
            <option value="0" <?= $promo['estado'] == 0 ? 'selected' : '' ?>>Inactiva</option>
        </select>

        <label class="font-bold text-blue-800">Imagen actual</label>
        <div class="flex items-center gap-4 mb-4">
            <img src="/data/promos/<?= $promo['dir_imagen'] ?>" width="120" class="rounded-xl shadow">
            <button onclick="abrirModalImagen()" class="bg-blue-600 text-white px-4 py-2 rounded">Cambiar imagen</button>
        </div>

        <button onclick="guardarEdicion()" class="w-full bg-orange-500 hover:bg-orange-600 text-white text-xl p-3 rounded-lg font-bold mt-6">
            Guardar cambios
        </button>

    </div>

    <!-- PREVIEW -->
    <div class="bg-white p-6 rounded-2xl w-2/5 h-[70vh] flex flex-col justify-center items-center">
        <img src="/data/promos/<?= $promo['dir_imagen'] ?>" width="320" class="rounded-xl shadow mb-6">
        <p class="text-2xl font-bold text-center"><?= htmlspecialchars($promo['nombre']) ?></p>
    </div>

</div>
</main>

<!-- MODAL CAMBIAR IMAGEN -->
<div id="modalImagen" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Cambiar imagen</h2>

        <input type="file" id="nuevaImg" accept="image/*" class="w-full mb-3">

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModal('modalImagen')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarImagen()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL SUCCESS -->
<div id="modalSuccess" class="modal-bg">
    <div class="modal text-center">
        <h2 class="text-xl font-bold text-green-600 mb-3">Guardado correctamente</h2>
        <p class="mb-4">Los cambios han sido aplicados.</p>
        <button onclick="location.reload()" class="bg-green-600 text-white px-6 py-2 rounded">OK</button>
    </div>
</div>

<!-- MODAL ERROR -->
<div id="modalError" class="modal-bg">
    <div class="modal text-center">
        <h2 class="text-xl font-bold text-red-600 mb-3">Error</h2>
        <p id="errorMsg" class="mb-4"></p>
        <button onclick="cerrarModal('modalError')" class="bg-red-600 text-white px-6 py-2 rounded">Cerrar</button>
    </div>
</div>

<script>
function abrirModal(id){ document.getElementById(id).style.display="flex"; }
function cerrarModal(id){ document.getElementById(id).style.display="none"; }
function abrirModalImagen(){ abrirModal("modalImagen"); }

function guardarEdicion(){
    let fd = new FormData();
    fd.append("id", "<?= $id ?>");
    fd.append("nombre", document.getElementById("nombre").value);
    fd.append("producto", document.getElementById("producto").value);
    fd.append("inicio", document.getElementById("inicio").value);
    fd.append("fin", document.getElementById("fin").value);
    fd.append("cliente", document.getElementById("cliente").value);
    fd.append("no_cliente", document.getElementById("no_cliente").value);
    fd.append("estado", document.getElementById("estado").value);

    fetch("/api/actualizarPromocion.controller.php", { method:"POST", body:fd })
    .then(r=>r.json())
    .then(d=>{
        if(d.status==="ok") abrirModal("modalSuccess");
        else mostrarError(d.msg);
    });
}

function guardarImagen(){
    let file = document.getElementById("nuevaImg").files[0];
    if(!file) return alert("Selecciona una imagen.");

    let fd = new FormData();
    fd.append("id", "<?= $id ?>");
    fd.append("imagen", file);

    fetch("/api/actualizarImagenPromocion.controller.php", { method:"POST", body:fd })
    .then(r=>r.json())
    .then(d=>{
        if(d.status==="ok") abrirModal("modalSuccess");
        else mostrarError(d.msg);
    });
}

function mostrarError(msg){
    document.getElementById("errorMsg").innerText = msg;
    abrirModal("modalError");
}
</script>
