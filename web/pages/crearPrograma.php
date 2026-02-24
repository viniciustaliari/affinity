<?php
include_once '../components/head.php';
require_once '../api/db.php';

$contextos = $database->select("contextos", ["id", "nombre"], [
  "ORDER" => ["id" => "ASC"]
]);
?>

<main class="px-3 relative">
<div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-6 rounded-2xl min-h-[100vh] flex justify-center">
<div class="bg-white w-4/5 p-10 rounded-2xl shadow-xl flex flex-col gap-8">

<h2 class="text-3xl font-bold text-center text-blue-700">
  Crear nuevo programa
</h2>

<input id="nombrePrograma"
       placeholder="Nombre del programa"
       class="p-3 border rounded-xl">

<textarea id="textoPrograma"
          placeholder="Texto del programa"
          class="p-3 border rounded-xl"
          rows="3"></textarea>

<select id="contextoPrograma" class="p-3 border rounded-xl">
  <?php foreach ($contextos as $ctx): ?>
    <option value="<?= (int)$ctx['id'] ?>">
      <?= htmlspecialchars($ctx['nombre']) ?>
    </option>
  <?php endforeach; ?>
</select>

<!-- BOTONES -->
<div class="flex gap-4">
  <button id="btnAddImagen"
          type="button"
          class="bg-green-500 text-white px-4 py-2 rounded">
    + Imagen
  </button>

  <button id="btnAddVideo"
          type="button"
          class="bg-purple-500 text-white px-4 py-2 rounded">
    + Video
  </button>
</div>

<!-- TIMELINE -->
<div id="contenedorTimeline" class="flex flex-col gap-4"></div>

<div class="flex gap-4">
  <button id="btnGuardarPrograma"
          type="button"
          class="bg-blue-600 text-white p-4 rounded-xl text-xl font-bold flex-1">
    Guardar
  </button>

  <button id="btnEnviarPrograma"
          type="button"
          class="bg-emerald-600 text-white p-4 rounded-xl text-xl font-bold flex-1">
    Enviar
  </button>
</div>

</div>
</div>
</main>

<!-- ================= MODAL ÉXITO ================= -->
<div id="modalExito"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl p-8 w-96 text-center shadow-xl">
    <h2 class="text-2xl font-bold text-green-600 mb-4">
      Programa creado
    </h2>
    <p id="modalExitoTexto" class="mb-6 text-gray-700">
      El programa se ha creado correctamente.
    </p>
    <button onclick="redirigirProgramas()"
            class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold">
      Ir a programas
    </button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
.preview-box {
  width: 220px;
  min-width: 220px;
  height: 160px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.preview-box img,
.preview-box video {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}
</style>

<script>
const timeline = document.getElementById("contenedorTimeline");
const IMAGE_ACCEPT_STR = ".jpg,.jpeg,.png,.webp,.gif,.bmp,.svg,image/jpeg,image/png,image/webp,image/gif,image/bmp,image/svg+xml";
const VIDEO_ACCEPT_STR = ".mp4,.webm,.mov,.avi,.mkv,.ogv,video/mp4,video/webm,video/quicktime,video/x-msvideo,video/x-matroska,video/ogg";
const IMAGE_EXTENSIONS = new Set(["jpg", "jpeg", "png", "webp", "gif", "bmp", "svg"]);
const VIDEO_EXTENSIONS = new Set(["mp4", "webm", "mov", "avi", "mkv", "ogv"]);

new Sortable(timeline, {
  animation: 150,
  handle: ".handle"
});

function fileExt(name) {
  const n = String(name || "");
  const idx = n.lastIndexOf(".");
  if (idx < 0) return "";
  return n.slice(idx + 1).toLowerCase();
}

function isImageFile(file) {
  const mime = String((file && file.type) || "").toLowerCase();
  if (mime.startsWith("image/")) return true;
  return IMAGE_EXTENSIONS.has(fileExt(file && file.name));
}

function isVideoFile(file) {
  const mime = String((file && file.type) || "").toLowerCase();
  if (mime.startsWith("video/")) return true;
  return VIDEO_EXTENSIONS.has(fileExt(file && file.name));
}

const inputFilesMemory = new WeakMap();

function updateInputFiles(input, validFiles, originalCount) {
  if (typeof DataTransfer !== "undefined") {
    const dt = new DataTransfer();
    validFiles.forEach((f) => dt.items.add(f));
    input.files = dt.files;
    return;
  }
  if (validFiles.length !== originalCount) {
    input.value = "";
  }
}

function filtrarArchivos(input, validator, label) {
  const files = Array.from(input.files || []);
  const remembered = inputFilesMemory.get(input) || [];

  if (files.length === 0) {
    if (remembered.length > 0) {
      updateInputFiles(input, remembered, remembered.length);
      return remembered;
    }
    return [];
  }

  const valid = files.filter(validator);
  const invalidCount = files.length - valid.length;
  if (invalidCount > 0) {
    alert(`Se omitieron ${invalidCount} archivo(s). Solo se permiten ${label}.`);
  }

  if (valid.length > 0) {
    inputFilesMemory.set(input, valid);
    updateInputFiles(input, valid, files.length);
    return valid;
  }

  if (remembered.length > 0) {
    updateInputFiles(input, remembered, remembered.length);
    return remembered;
  }

  updateInputFiles(input, [], files.length);
  inputFilesMemory.set(input, []);
  return [];
}

/* ===== PREVIEW ===== */
function previewImagen(input) {
  const bloque = input.closest(".bloque");
  const img = bloque.querySelector(".previewImg");
  const info = bloque.querySelector(".mediaCountInfo");
  const files = filtrarArchivos(input, isImageFile, "imagenes");
  if (files.length === 0) {
    img.removeAttribute("src");
    img.classList.add("hidden");
    if (info) info.textContent = "";
    return;
  }

  const file = files[0];
  img.src = URL.createObjectURL(file);
  img.classList.remove("hidden");

  if (info) {
    info.textContent = files.length === 1
      ? "1 imagen seleccionada"
      : `${files.length} imagenes seleccionadas`;
  }
}

function previewVideo(input) {
  const bloque = input.closest(".bloque");
  const video = bloque.querySelector(".previewVideo");
  const info = bloque.querySelector(".mediaCountInfo");
  const files = filtrarArchivos(input, isVideoFile, "videos");
  if (files.length === 0) {
    video.removeAttribute("src");
    video.classList.add("hidden");
    if (info) info.textContent = "";
    return;
  }

  const file = files[0];
  video.src = URL.createObjectURL(file);
  video.classList.remove("hidden");

  if (info) {
    info.textContent = files.length === 1
      ? "1 video seleccionado"
      : `${files.length} videos seleccionados`;
  }
}

/* ===== BLOQUES ===== */
function addBloqueImagen() {
  timeline.insertAdjacentHTML("beforeend", `
  <div class="bloque p-4 border rounded-xl flex gap-4 items-start"
       data-tipo="imagen">

    <div class="flex-1 flex flex-col gap-2">
      <div class="handle cursor-grab text-gray-500 font-bold">≡ Imagen</div>

	      <input type="file" class="archivoImagen" accept="${IMAGE_ACCEPT_STR}" multiple
	             onchange="previewImagen(this)">
	      <p class="mediaCountInfo text-xs text-slate-500"></p>

      <input type="number" class="duracionImg" placeholder="Duración (seg)">

      <select class="fitImg">
        <option value="cover">Cover</option>
        <option value="contain">Contain</option>
      </select>

      <select class="transitionImg">
        <option value="fade">Fade</option>
        <option value="none">None</option>
      </select>

      <button onclick="this.closest('.bloque').remove()"
              class="bg-red-500 text-white px-2 py-1 rounded w-fit">
        Eliminar
      </button>
    </div>

    <div class="preview-box border rounded">
      <img class="previewImg hidden">
    </div>
  </div>
  `);
}

function addBloqueVideo() {
  timeline.insertAdjacentHTML("beforeend", `
  <div class="bloque p-4 border rounded-xl flex gap-4 items-start"
       data-tipo="video">

    <div class="flex-1 flex flex-col gap-2">
      <div class="handle cursor-grab text-gray-500 font-bold">≡ Video</div>

	      <input type="file" class="archivoVideo" accept="${VIDEO_ACCEPT_STR}" multiple
	             onchange="previewVideo(this)">
	      <p class="mediaCountInfo text-xs text-slate-500"></p>

      <input type="number" class="duracionVideo" placeholder="Duración (seg)">
      <input type="number" class="repeatVideo" placeholder="Repeticiones">

      <label class="flex items-center gap-2">
        <input type="checkbox" class="muteVideo"> Mute
      </label>

      <button onclick="this.closest('.bloque').remove()"
              class="bg-red-500 text-white px-2 py-1 rounded w-fit">
        Eliminar
      </button>
    </div>

    <div class="preview-box border rounded">
      <video class="previewVideo hidden" controls></video>
    </div>
  </div>
  `);
}

/* ===== BOTONES ===== */
const btnAddImagen = document.getElementById("btnAddImagen");
const btnAddVideo = document.getElementById("btnAddVideo");
const btnGuardarPrograma = document.getElementById("btnGuardarPrograma");
const btnEnviarPrograma = document.getElementById("btnEnviarPrograma");
const nombreProgramaInput = document.getElementById("nombrePrograma");
const textoProgramaInput = document.getElementById("textoPrograma");
btnAddImagen.onclick = addBloqueImagen;
btnAddVideo.onclick = addBloqueVideo;
const contextoProgramaSelect = document.getElementById("contextoPrograma");
let wsCreateUI = null;
let wsCreateReady = false;
let pendingPush = null;

function conectarWsCreateUI() {
  const proto = window.location.protocol === "https:" ? "wss" : "ws";
  const url = `${proto}://${window.location.hostname}:8090?role=ui&source=crear_programa`;

  wsCreateUI = new WebSocket(url);

  wsCreateUI.onopen = () => {
    wsCreateReady = true;
  };

  wsCreateUI.onclose = () => {
    wsCreateReady = false;
    if (pendingPush && pendingPush.reject) {
      pendingPush.reject("Conexion WS cerrada");
      pendingPush = null;
    }
    setTimeout(conectarWsCreateUI, 3000);
  };

  wsCreateUI.onerror = () => {
    wsCreateReady = false;
  };

  wsCreateUI.onmessage = (event) => {
    let msg = null;
    try {
      msg = JSON.parse(event.data);
    } catch (e) {
      return;
    }

    if (!msg || msg.type !== "push_program_result" || !pendingPush) return;
    if (Number(msg.program_id) !== Number(pendingPush.programId)) return;

    clearTimeout(pendingPush.timerId);
    const resolve = pendingPush.resolve;
    pendingPush = null;
    resolve(msg);
  };
}

function pushProgramaPorWs(programId) {
  return new Promise((resolve, reject) => {
    if (!wsCreateUI || !wsCreateReady || wsCreateUI.readyState !== WebSocket.OPEN) {
      reject("Conexion WebSocket no disponible");
      return;
    }

    if (pendingPush) {
      reject("Ya hay un envio en curso");
      return;
    }

    const timerId = setTimeout(() => {
      if (!pendingPush) return;
      const rej = pendingPush.reject;
      pendingPush = null;
      rej("Timeout esperando respuesta WS");
    }, 12000);

    pendingPush = {
      programId: Number(programId),
      resolve,
      reject,
      timerId
    };

    wsCreateUI.send(JSON.stringify({
      type: "push_program",
      program_id: Number(programId)
    }));
  });
}

conectarWsCreateUI();

/* ===== ACCIONES ===== */
function construirFormDataPrograma() {
  if (!nombreProgramaInput.value.trim()) {
    alert("El nombre del programa es obligatorio");
    return null;
  }
  if (!contextoProgramaSelect || !contextoProgramaSelect.value) {
    alert("Debes seleccionar un contexto");
    return null;
  }

  const fd = new FormData();
  fd.append("nombre", nombreProgramaInput.value.trim());
  fd.append("texto", textoProgramaInput.value);
  fd.append("contexto", contextoProgramaSelect.value);

  let orden = 0;

  timeline.querySelectorAll(".bloque").forEach(b => {
    if (b.dataset.tipo === "imagen") {
      const input = b.querySelector(".archivoImagen");
      const files = filtrarArchivos(input, isImageFile, "imagenes");
      if (files.length === 0) return;

      const duracion = b.querySelector(".duracionImg").value;
      const fit = b.querySelector(".fitImg").value;
      const transition = b.querySelector(".transitionImg").value;

      files.forEach((f) => {
        fd.append("imagen_" + orden, f, f.name);
        fd.append("duracionImg_" + orden, duracion);
        fd.append("indiceImg_" + orden, orden);
        fd.append("fitImg_" + orden, fit);
        fd.append("transitionImg_" + orden, transition);
        orden++;
      });
      return;
    }

    if (b.dataset.tipo === "video") {
      const input = b.querySelector(".archivoVideo");
      const files = filtrarArchivos(input, isVideoFile, "videos");
      if (files.length === 0) return;

      const duracion = b.querySelector(".duracionVideo").value;
      const repeat = b.querySelector(".repeatVideo").value;
      const mute = b.querySelector(".muteVideo").checked ? 1 : 0;

      files.forEach((f) => {
        fd.append("video_" + orden, f, f.name);
        fd.append("duracionVideo_" + orden, duracion);
        fd.append("indiceVideo_" + orden, orden);
        fd.append("repeatVideo_" + orden, repeat);
        fd.append("muteVideo_" + orden, mute);
        orden++;
      });
    }
  });

  return fd;
}

function setBotonesAccion(disabled) {
  [btnGuardarPrograma, btnEnviarPrograma].forEach((btn) => {
    btn.disabled = disabled;
    btn.classList.toggle("opacity-60", disabled);
    btn.classList.toggle("cursor-not-allowed", disabled);
  });
}

async function crearProgramaEnServidor() {
  const fd = construirFormDataPrograma();
  if (!fd) return null;

  const r = await fetch("../api/crearPrograma.controller.php", {
    method: "POST",
    body: fd
  });

  let data = null;
  try {
    data = await r.json();
  } catch (e) {
    throw new Error("Respuesta invalida del servidor");
  }

  if (!r.ok || !data || data.status !== "ok") {
    const detalle = data && data.package_error ? ` (${data.package_error})` : "";
    throw new Error("Error al crear el programa" + detalle);
  }

  return data;
}

function mostrarModalExito(mensaje) {
  const modalText = document.getElementById("modalExitoTexto");
  if (modalText) {
    modalText.textContent = mensaje;
  }

  document.getElementById("modalExito").classList.remove("hidden");
  document.getElementById("modalExito").classList.add("flex");
}

btnGuardarPrograma.onclick = async () => {
  setBotonesAccion(true);
  try {
    const data = await crearProgramaEnServidor();
    if (!data) return;
    redirigirProgramas();
  } catch (err) {
    alert(String(err.message || err));
  } finally {
    setBotonesAccion(false);
  }
};

btnEnviarPrograma.onclick = async () => {
  setBotonesAccion(true);
  try {
    const data = await crearProgramaEnServidor();
    if (!data) return;

    let mensaje = "El programa se ha guardado correctamente.";

    if (data.package_saved) {
      const sizeKb = data.zip_size_bytes
        ? (Number(data.zip_size_bytes) / 1024).toFixed(1)
        : "0.0";
      mensaje += ` ZIP guardado en BD: ${data.zip_name || "program.zip"} (${sizeKb} KB).`;
    }

    try {
      const pushRes = await pushProgramaPorWs(Number(data.id_programa));
      if (pushRes && pushRes.status === "ok") {
        const enviados = Number(pushRes.clients_sent || 0);
        const receivers = Number(pushRes.receivers_connected || 0);
        mensaje += ` Enviado por WS. Receptores conectados: ${receivers}. Clientes notificados: ${enviados}.`;
      } else {
        const errWs = pushRes && pushRes.error ? String(pushRes.error) : "error desconocido";
        mensaje += ` Guardado, pero no se pudo enviar por WS (${errWs}).`;
      }
    } catch (wsErr) {
      mensaje += ` Guardado, pero no se pudo enviar por WS (${String(wsErr)}).`;
    }

    mostrarModalExito(mensaje);
  } catch (err) {
    alert(String(err.message || err));
  } finally {
    setBotonesAccion(false);
  }
};

function redirigirProgramas() {
  location.href = "/pages/programas.php";
}
</script>

