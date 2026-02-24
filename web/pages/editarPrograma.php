<?php 
include_once '../components/head.php';
require_once '../api/db.php';

$id_programa = $_GET['id'] ?? null;
if (!$id_programa) die("Falta el ID del programa.");

// Programa
$programa = $database->get("programas", "*", ["id" => $id_programa]);

// Imágenes
$imagenes = $database->select("imagenes", "*", [
    "id_programa" => $id_programa
]);

// Videos
$videos = $database->select("video", "*", [
    "id_programa" => $id_programa
]);

// Contextos
$contextos = $database->select("contextos", "*");

/* =========================
   TIMELINE UNIFICADA
========================= */
$timeline = [];

foreach ($imagenes as $img) {
    $img['tipo'] = 'imagen';
    $timeline[] = $img;
}

foreach ($videos as $vid) {
    $vid['tipo'] = 'video';
    $timeline[] = $vid;
}

usort($timeline, fn($a, $b) => ($a['indice'] ?? 0) <=> ($b['indice'] ?? 0));
?>

<style>
.modal-bg {
    position: fixed; inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none; 
    justify-content: center; 
    align-items: center;
    z-index: 1000;
}
.modal {
    background: white;
    padding: 25px;
    border-radius: 16px;
    width: 440px;
}
.handle{
    cursor: grab;
    user-select: none;
    font-weight: 900;
    color: #6b7280; /* gray-500 */
    padding: 0 8px;
    font-size: 20px;
    line-height: 1;
}
.timeline-item{
    user-select: none;
}
</style>

<main class="px-3 ">

<div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl flex h-[80vh] gap-10 justify-center items-center">

    <!-- IZQUIERDA -->
    <div class="flex flex-col w-3/5 ms-10 bg-white rounded-2xl p-5 gap-5 h-[70vh] overflow-y-auto">

        <h2 class="text-2xl font-bold mb-2 text-blue-800">
            Editando: <?= htmlspecialchars($programa['nombre']) ?>
        </h2>

        <!-- TEXTO (NO DRAG) -->
        <div class="w-full bg-gray-100 p-3 rounded-xl flex items-center gap-3">
            <img src="../assets/img/dialog.png" width="40">
            <p class="font-bold text-xl flex-1"><?= htmlspecialchars($programa['texto']) ?></p>
            <button class="bg-blue-600 text-white px-3 py-1 rounded" onclick="abrirModalTexto()">
                Editar
            </button>
        </div>

        <!-- TIMELINE DRAGGABLE (IMAGENES + VIDEOS) -->
        <div id="timelineList" class="flex flex-col gap-5">

            <?php foreach ($timeline as $item): ?>

                <?php if ($item['tipo'] === 'imagen'): ?>
                <div class="timeline-item w-full bg-gray-100 p-3 rounded-xl flex items-center gap-4"
                    data-tipo="imagen"
                    data-id="<?= (int)$item['id'] ?>">

                    <div class="handle" title="Arrastra para reordenar">≡</div>

                    <img src="/data/images/<?= htmlspecialchars($item['nombre']) ?>" width="80" class="rounded-lg shadow">

                    <div class="flex flex-col flex-1">
                        <p class="font-bold text-lg"><?= htmlspecialchars($item['nombre']) ?></p>
                        <p class="text-sm text-gray-600">
                            Duración: <?= htmlspecialchars($item['duracion']) ?>s ·
                            Orden: <?= htmlspecialchars($item['indice'] ?? 0) ?> ·
                            Fit: <?= htmlspecialchars($item['fit'] ?? 'cover') ?> ·
                            Transición: <?= htmlspecialchars($item['transition'] ?? 'fade') ?>
                        </p>
                    </div>

                    <button
                        class="bg-blue-600 text-white px-3 py-1 rounded btnEditarImagen"
                        data-id="<?= (int)$item['id'] ?>"
                        data-duracion="<?= htmlspecialchars($item['duracion']) ?>"
                        data-indice="<?= htmlspecialchars($item['indice'] ?? 0) ?>"
                        data-fit="<?= htmlspecialchars($item['fit'] ?? 'cover') ?>"
                        data-transition="<?= htmlspecialchars($item['transition'] ?? 'fade') ?>"
                    >
                        Editar
                    </button>

                    <button class="bg-red-600 text-white px-3 py-1 rounded"
                            onclick="confirmarBorrarImagen(<?= (int)$item['id'] ?>)">
                        Borrar
                    </button>

                </div>
                <?php else: ?>
                <div class="timeline-item w-full bg-gray-100 p-3 rounded-xl flex items-center gap-4"
                    data-tipo="video"
                    data-id="<?= (int)$item['id'] ?>">

                    <div class="handle" title="Arrastra para reordenar">≡</div>

                    <img src="../assets/img/video.png" width="60">

                    <div class="flex flex-col flex-1">
                        <p class="font-bold text-lg"><?= htmlspecialchars($item['nombre']) ?></p>
                        <p class="text-sm text-gray-600">
                            Duración: <?= htmlspecialchars($item['duracion']) ?>s ·
                            Orden: <?= htmlspecialchars($item['indice'] ?? 0) ?> ·
                            Repeat: <?= htmlspecialchars($item['repeat'] ?? '') ?> ·
                            Mute: <?= !empty($item['mute']) ? 'sí' : 'no' ?>
                        </p>
                    </div>

                    <button
                        class="bg-blue-600 text-white px-3 py-1 rounded btnEditarVideo"
                        data-id="<?= (int)$item['id'] ?>"
                        data-duracion="<?= htmlspecialchars($item['duracion']) ?>"
                        data-indice="<?= htmlspecialchars($item['indice'] ?? 0) ?>"
                        data-repeat="<?= htmlspecialchars($item['repeat'] ?? '') ?>"
                        data-mute="<?= !empty($item['mute']) ? '1' : '0' ?>"
                    >
                        Editar
                    </button>

                    <button class="bg-red-600 text-white px-3 py-1 rounded"
                            onclick="confirmarBorrarVideo(<?= (int)$item['id'] ?>)">
                        Borrar
                    </button>

                </div>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    </div>

    <!-- DERECHA -->
    <div class="flex flex-col w-2/5 gap-5">

        <div onclick="abrirModalNuevaImagen()"
             class="bg-white rounded-2xl p-3 flex items-center gap-3 cursor-pointer shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/plus.png" width="50">
            <p class="font-bold text-2xl">Añadir imagen</p>
        </div>

        <div onclick="abrirModalTexto()"
             class="bg-white rounded-2xl p-3 flex items-center gap-3 cursor-pointer shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/edit_txt.png" width="50">
            <p class="font-bold text-2xl">Editar texto</p>
        </div>

        <div onclick="abrirModalNuevoVideo()"
             class="bg-white rounded-2xl p-3 flex items-center gap-3 cursor-pointer shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/add_video.png" width="50">
            <p class="font-bold text-2xl">Añadir video</p>
        </div>

        <div onclick="abrirModalContexto()"
             class="bg-white rounded-2xl p-3 flex items-center gap-3 cursor-pointer shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/context.png" width="50">
            <p class="font-bold text-2xl">Editar contexto</p>
        </div>

        <div onclick="abrirModalBorrarPrograma()"
            class="bg-white rounded-2xl p-3 flex flex-row items-center gap-3 cursor-pointer justify-left shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/trash.png" width="50">
            <p class="font-bold text-2xl">Borrar programa</p>
        </div>

        <a href="/pages/programas.php" 
           class="bg-white rounded-2xl p-3 flex items-center gap-3 shadow hover:bg-blue-800 hover:text-white">
            <img src="../assets/img/save.png" width="50">
            <p class="font-bold text-2xl">Guardar y salir</p>
        </a>

    </div>

</div>
</main>

<!-- ============================== -->
<!-- MODALES -->
<!-- ============================== -->

<div id="modalBorrarPrograma" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3 text-red-600">¿Borrar programa?</h2>

        <p class="mb-4">Esta acción eliminará el programa, sus imágenes y videos. No se puede deshacer.</p>

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModal('modalBorrarPrograma')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="borrarProgramaConfirmado()" class="bg-red-600 text-white px-4 py-2 rounded">Eliminar</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR TEXTO -->
<div id="modalTexto" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Editar texto</h2>
        <textarea id="nuevoTexto" class="w-full p-2 border rounded" rows="4"><?= htmlspecialchars($programa['texto']) ?></textarea>
        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalTexto')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarTexto()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL NUEVA IMAGEN -->
<div id="modalNuevaImagen" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Añadir imagen</h2>

        <input type="file" id="nuevaImagenFile" accept=".jpg,.jpeg,.png,.webp,.gif,.bmp,.svg,image/jpeg,image/png,image/webp,image/gif,image/bmp,image/svg+xml" class="w-full mb-3">

        <label class="font-bold">Duración (segundos):</label>
        <input type="number" id="nuevaImagenDur" class="w-full p-2 border rounded mb-3" min="0.1" step="0.1">

        <label class="font-bold">Orden (index):</label>
        <input type="number" id="nuevaImagenIndice" class="w-full p-2 border rounded mb-3" min="0">

        <label class="font-bold">Fit:</label>
        <select id="nuevaImagenFit" class="w-full p-2 border rounded mb-3">
            <option value="cover">cover</option>
            <option value="contain">contain</option>
        </select>

        <label class="font-bold">Transición:</label>
        <select id="nuevaImagenTransition" class="w-full p-2 border rounded">
            <option value="fade">fade</option>
            <option value="none">none</option>
        </select>

        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalNuevaImagen')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarNuevaImagen()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR IMAGEN -->
<div id="modalEditarImagen" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Editar imagen</h2>

        <input type="hidden" id="editImagenID">

        <label class="font-bold">Duración (segundos):</label>
        <input type="number" id="editImagenDur" class="w-full p-2 border rounded mb-3" min="0.1" step="0.1">

        <label class="font-bold">Orden (index):</label>
        <input type="number" id="editImagenIndice" class="w-full p-2 border rounded mb-3" min="0">

        <label class="font-bold">Fit:</label>
        <select id="editImagenFit" class="w-full p-2 border rounded mb-3">
            <option value="cover">cover</option>
            <option value="contain">contain</option>
        </select>

        <label class="font-bold">Transición:</label>
        <select id="editImagenTransition" class="w-full p-2 border rounded">
            <option value="fade">fade</option>
            <option value="none">none</option>
        </select>

        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalEditarImagen')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarEditarImagen()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL BORRAR IMAGEN -->
<div id="modalBorrarImagen" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3 text-red-600">¿Borrar imagen?</h2>

        <input type="hidden" id="deleteImagenID">

        <p class="mb-4">Esta acción no se puede deshacer.</p>

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModal('modalBorrarImagen')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="borrarImagenConfirmado()" class="bg-red-600 text-white px-4 py-2 rounded">Eliminar</button>
        </div>
    </div>
</div>

<!-- MODAL NUEVO VIDEO -->
<div id="modalNuevoVideo" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Añadir video</h2>

        <input type="file" id="nuevoVideoFile" accept=".mp4,.webm,.mov,.avi,.mkv,.ogv,video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska,video/ogg" class="w-full mb-3">

        <label class="font-bold">Duración (segundos):</label>
        <input type="number" id="nuevoVideoDur" class="w-full p-2 border rounded mb-3" min="0.1" step="0.1">

        <label class="font-bold">Orden (index):</label>
        <input type="number" id="nuevoVideoIndice" class="w-full p-2 border rounded mb-3" min="0">

        <label class="font-bold">Repeticiones (repeat):</label>
        <input type="number" id="nuevoVideoRepeat" class="w-full p-2 border rounded mb-3" min="1">

        <label class="font-bold flex items-center gap-2">
            <input type="checkbox" id="nuevoVideoMute">
            Mute
        </label>

        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalNuevoVideo')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarNuevoVideo()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR VIDEO -->
<div id="modalEditarVideo" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Editar video</h2>

        <input type="hidden" id="editVideoID">

        <label class="font-bold">Duración (segundos):</label>
        <input type="number" id="editVideoDur" class="w-full p-2 border rounded mb-3" min="0.1" step="0.1">

        <label class="font-bold">Orden (index):</label>
        <input type="number" id="editVideoIndice" class="w-full p-2 border rounded mb-3" min="0">

        <label class="font-bold">Repeticiones (repeat):</label>
        <input type="number" id="editVideoRepeat" class="w-full p-2 border rounded mb-3" min="1">

        <label class="font-bold flex items-center gap-2">
            <input type="checkbox" id="editVideoMute">
            Mute
        </label>

        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalEditarVideo')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarEditarVideo()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<!-- MODAL BORRAR VIDEO -->
<div id="modalBorrarVideo" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3 text-red-600">¿Borrar video?</h2>

        <input type="hidden" id="deleteVideoID">

        <p class="mb-4">Esta acción no se puede deshacer.</p>

        <div class="flex justify-end gap-3">
            <button onclick="cerrarModal('modalBorrarVideo')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="borrarVideoConfirmado()" class="bg-red-600 text-white px-4 py-2 rounded">Eliminar</button>
        </div>
    </div>
</div>

<!-- MODAL EDITAR CONTEXTO -->
<div id="modalContexto" class="modal-bg">
    <div class="modal">
        <h2 class="text-xl font-bold mb-3">Editar contexto</h2>

        <select id="nuevoContexto" class="w-full p-2 border rounded">
            <?php foreach ($contextos as $ctx): ?>
                <option value="<?= (int)$ctx['id'] ?>"
                    <?= $ctx['id'] == $programa['contexto'] ? "selected" : "" ?>>
                    <?= htmlspecialchars($ctx['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="mt-4 flex justify-end gap-3">
            <button onclick="cerrarModal('modalContexto')" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            <button onclick="guardarContexto()" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
function abrirModal(id) { document.getElementById(id).style.display = "flex"; }
function cerrarModal(id) { document.getElementById(id).style.display = "none"; }

function abrirModalTexto() { abrirModal('modalTexto'); }
function abrirModalNuevaImagen() { abrirModal('modalNuevaImagen'); }
function abrirModalNuevoVideo() { abrirModal('modalNuevoVideo'); }
function abrirModalContexto() { abrirModal('modalContexto'); }
function abrirModalBorrarPrograma() { abrirModal('modalBorrarPrograma'); }

function confirmarBorrarImagen(id) {
    document.getElementById('deleteImagenID').value = id;
    abrirModal('modalBorrarImagen');
}
function confirmarBorrarVideo(id) {
    document.getElementById('deleteVideoID').value = id;
    abrirModal('modalBorrarVideo');
}

/* ==========================
   EVENT DELEGATION (FIX)
   Los botones no se rompen aunque se mueva el DOM
========================== */
document.addEventListener("click", (e) => {
    const btnImg = e.target.closest(".btnEditarImagen");
    if (btnImg) {
        document.getElementById('editImagenID').value = btnImg.dataset.id;
        document.getElementById('editImagenDur').value = btnImg.dataset.duracion;
        document.getElementById('editImagenIndice').value = btnImg.dataset.indice;
        document.getElementById('editImagenFit').value = btnImg.dataset.fit || 'cover';
        document.getElementById('editImagenTransition').value = btnImg.dataset.transition || 'fade';
        abrirModal('modalEditarImagen');
        return;
    }

    const btnVid = e.target.closest(".btnEditarVideo");
    if (btnVid) {
        document.getElementById('editVideoID').value = btnVid.dataset.id;
        document.getElementById('editVideoDur').value = btnVid.dataset.duracion;
        document.getElementById('editVideoIndice').value = btnVid.dataset.indice;
        document.getElementById('editVideoRepeat').value = btnVid.dataset.repeat || '';
        document.getElementById('editVideoMute').checked = (btnVid.dataset.mute === '1');
        abrirModal('modalEditarVideo');
        return;
    }
});

/* ==========================
   DRAG & DROP + GUARDAR ORDEN
========================== */
new Sortable(document.getElementById("timelineList"), {
    animation: 150,
    handle: ".handle",
    onEnd: guardarOrdenTimeline
});

function guardarOrdenTimeline() {
    const items = [];
    document.querySelectorAll("#timelineList .timeline-item").forEach((el, index) => {
        items.push({
            id: el.dataset.id,
            tipo: el.dataset.tipo,
            indice: index
        });
    });

    const fd = new FormData();
    items.forEach((it, i) => {
        fd.append(`items[${i}][id]`, it.id);
        fd.append(`items[${i}][tipo]`, it.tipo);
        fd.append(`items[${i}][indice]`, it.indice);
    });

    fetch("/api/actualizarOrden.controller.php", { method: "POST", body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === "ok") location.reload();
            else alert("Error al guardar orden: " + (d.msg ?? d.mensaje ?? "error"));
        })
        .catch(() => alert("Error de conexión guardando el orden"));
}

/* Guardar texto */
function guardarTexto() {
    const texto = document.getElementById("nuevoTexto").value;

    const fd = new FormData();
    fd.append("id_programa", "<?= (int)$id_programa ?>");
    fd.append("texto", texto);

    fetch("/api/actualizarTexto.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

const IMAGE_EXTENSIONS = new Set(["jpg", "jpeg", "png", "webp", "gif", "bmp", "svg"]);
const VIDEO_EXTENSIONS = new Set(["mp4", "webm", "mov", "avi", "mkv", "ogv"]);

function getFileExt(fileName) {
    const n = String(fileName || "");
    const i = n.lastIndexOf(".");
    if (i < 0) return "";
    return n.slice(i + 1).toLowerCase();
}

function isValidImageFile(file) {
    const mime = String((file && file.type) || "").toLowerCase();
    if (mime.startsWith("image/")) return true;
    return IMAGE_EXTENSIONS.has(getFileExt(file && file.name));
}

function isValidVideoFile(file) {
    const mime = String((file && file.type) || "").toLowerCase();
    if (mime.startsWith("video/")) return true;
    return VIDEO_EXTENSIONS.has(getFileExt(file && file.name));
}

const nuevaImagenInput = document.getElementById("nuevaImagenFile");
if (nuevaImagenInput) {
    nuevaImagenInput.addEventListener("change", () => {
        const f = nuevaImagenInput.files && nuevaImagenInput.files[0];
        if (f && !isValidImageFile(f)) {
            alert("Solo se permiten imágenes en esta opción.");
            nuevaImagenInput.value = "";
        }
    });
}

const nuevoVideoInput = document.getElementById("nuevoVideoFile");
if (nuevoVideoInput) {
    nuevoVideoInput.addEventListener("change", () => {
        const f = nuevoVideoInput.files && nuevoVideoInput.files[0];
        if (f && !isValidVideoFile(f)) {
            alert("Solo se permiten videos en esta opción.");
            nuevoVideoInput.value = "";
        }
    });
}

/* Nueva imagen */
function guardarNuevaImagen() {
    const file = document.getElementById("nuevaImagenFile").files[0];
    const dur = document.getElementById("nuevaImagenDur").value;
    const indice = document.getElementById("nuevaImagenIndice").value;
    const fit = document.getElementById("nuevaImagenFit").value;
    const transition = document.getElementById("nuevaImagenTransition").value;

    if (!file) { alert("Selecciona una imagen"); return; }
    if (!isValidImageFile(file)) {
        alert("El archivo seleccionado no es una imagen válida.");
        return;
    }

    const fd = new FormData();
    fd.append("id_programa", "<?= (int)$id_programa ?>");
    fd.append("duracion", dur);
    fd.append("indice", indice);
    fd.append("fit", fit);
    fd.append("transition", transition);
    fd.append("imagen", file);

    fetch("/api/nuevaImagen.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Editar imagen */
function guardarEditarImagen() {
    const id = document.getElementById("editImagenID").value;
    const dur = document.getElementById("editImagenDur").value;
    const indice = document.getElementById("editImagenIndice").value;
    const fit = document.getElementById("editImagenFit").value;
    const transition = document.getElementById("editImagenTransition").value;

    const fd = new FormData();
    fd.append("id_imagen", id);
    fd.append("duracion", dur);
    fd.append("indice", indice);
    fd.append("fit", fit);
    fd.append("transition", transition);

    fetch("/api/editarImagen.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Borrar imagen */
function borrarImagenConfirmado() {
    const id = document.getElementById("deleteImagenID").value;

    const fd = new FormData();
    fd.append("id_imagen", id);

    fetch("/api/borrarImagen.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Nuevo video */
function guardarNuevoVideo() {
    const file = document.getElementById("nuevoVideoFile").files[0];
    const dur = document.getElementById("nuevoVideoDur").value;
    const indice = document.getElementById("nuevoVideoIndice").value;
    const repeat = document.getElementById("nuevoVideoRepeat").value;
    const mute = document.getElementById("nuevoVideoMute").checked ? 1 : 0;

    if (!file) { alert("Selecciona un video"); return; }
    if (!isValidVideoFile(file)) {
        alert("El archivo seleccionado no es un video válido.");
        return;
    }

    const fd = new FormData();
    fd.append("id_programa", "<?= (int)$id_programa ?>");
    fd.append("duracion", dur);
    fd.append("indice", indice);
    fd.append("repeat", repeat);
    fd.append("mute", mute);
    fd.append("video", file);

    fetch("/api/nuevoVideo.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Editar video */
function guardarEditarVideo() {
    const id = document.getElementById("editVideoID").value;
    const dur = document.getElementById("editVideoDur").value;
    const indice = document.getElementById("editVideoIndice").value;
    const repeat = document.getElementById("editVideoRepeat").value;
    const mute = document.getElementById("editVideoMute").checked ? 1 : 0;

    const fd = new FormData();
    fd.append("id_video", id);
    fd.append("duracion", dur);
    fd.append("indice", indice);
    fd.append("repeat", repeat);
    fd.append("mute", mute);

    fetch("/api/editarVideo.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Borrar video */
function borrarVideoConfirmado() {
    const id = document.getElementById("deleteVideoID").value;

    const fd = new FormData();
    fd.append("id_video", id);

    fetch("/api/borrarVideo.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Contexto */
function guardarContexto() {
    const nuevo = document.getElementById("nuevoContexto").value;

    const fd = new FormData();
    fd.append("id_programa", "<?= (int)$id_programa ?>");
    fd.append("contexto", nuevo);

    fetch("/api/actualizarContexto.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => d.status === "ok" ? location.reload() : alert("Error: " + (d.msg ?? d.mensaje ?? "error")));
}

/* Borrar programa */
function borrarProgramaConfirmado() {
    const fd = new FormData();
    fd.append("id_programa", "<?= (int)$id_programa ?>");

    fetch("/api/borrarPrograma.controller.php", { method: "POST", body: fd })
    .then(r => r.json())
    .then(d => {
        if (d.status === "ok") window.location.href = "/pages/programas.php";
        else alert("Error: " + (d.msg ?? d.mensaje ?? "error"));
    });
}
</script>
