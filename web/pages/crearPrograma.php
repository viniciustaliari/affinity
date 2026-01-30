<?php include_once '../components/head.php'; ?>

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

<!-- BOTONES -->
<div class="flex gap-4">
  <button id="btnAddImagen"
          class="bg-green-500 text-white px-4 py-2 rounded">
    + Imagen
  </button>

  <button id="btnAddVideo"
          class="bg-purple-500 text-white px-4 py-2 rounded">
    + Video
  </button>
</div>

<!-- TIMELINE -->
<div id="contenedorTimeline" class="flex flex-col gap-4"></div>

<button id="btnCrearPrograma"
        class="bg-blue-600 text-white p-4 rounded-xl text-xl font-bold">
  Crear programa
</button>

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
    <p class="mb-6 text-gray-700">
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

new Sortable(timeline, {
  animation: 150,
  handle: ".handle"
});

/* ===== PREVIEW ===== */
function previewImagen(input) {
  const file = input.files[0];
  if (!file) return;
  const img = input.closest(".bloque").querySelector(".previewImg");
  img.src = URL.createObjectURL(file);
  img.classList.remove("hidden");
}

function previewVideo(input) {
  const file = input.files[0];
  if (!file) return;
  const video = input.closest(".bloque").querySelector(".previewVideo");
  video.src = URL.createObjectURL(file);
  video.classList.remove("hidden");
}

/* ===== BLOQUES ===== */
function añadirBloqueImagen() {
  timeline.insertAdjacentHTML("beforeend", `
  <div class="bloque p-4 border rounded-xl flex gap-4 items-start"
       data-tipo="imagen">

    <div class="flex-1 flex flex-col gap-2">
      <div class="handle cursor-grab text-gray-500 font-bold">≡ Imagen</div>

      <input type="file" class="archivoImagen" accept="image/*"
             onchange="previewImagen(this)">

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

function añadirBloqueVideo() {
  timeline.insertAdjacentHTML("beforeend", `
  <div class="bloque p-4 border rounded-xl flex gap-4 items-start"
       data-tipo="video">

    <div class="flex-1 flex flex-col gap-2">
      <div class="handle cursor-grab text-gray-500 font-bold">≡ Video</div>

      <input type="file" class="archivoVideo" accept="video/*"
             onchange="previewVideo(this)">

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
btnAddImagen.onclick = añadirBloqueImagen;
btnAddVideo.onclick = añadirBloqueVideo;

/* ===== ENVÍO ===== */
btnCrearPrograma.onclick = async () => {

  if (!nombrePrograma.value.trim()) {
    alert("El nombre del programa es obligatorio");
    return;
  }

  const fd = new FormData();
  fd.append("nombre", nombrePrograma.value);
  fd.append("texto", textoPrograma.value);
  fd.append("contexto", 1);

  let orden = 0;

  timeline.querySelectorAll(".bloque").forEach(b => {

    if (b.dataset.tipo === "imagen") {
      const f = b.querySelector(".archivoImagen").files[0];
      if (!f) return;

      fd.append("imagen_" + orden, f);
      fd.append("duracionImg_" + orden, b.querySelector(".duracionImg").value);
      fd.append("indiceImg_" + orden, orden);
      fd.append("fitImg_" + orden, b.querySelector(".fitImg").value);
      fd.append("transitionImg_" + orden, b.querySelector(".transitionImg").value);
    }

    if (b.dataset.tipo === "video") {
      const f = b.querySelector(".archivoVideo").files[0];
      if (!f) return;

      fd.append("video_" + orden, f);
      fd.append("duracionVideo_" + orden, b.querySelector(".duracionVideo").value);
      fd.append("indiceVideo_" + orden, orden);
      fd.append("repeatVideo_" + orden, b.querySelector(".repeatVideo").value);
      fd.append("muteVideo_" + orden,
                b.querySelector(".muteVideo").checked ? 1 : 0);
    }

    orden++;
  });

  const r = await fetch("../api/crearPrograma.controller.php", {
    method: "POST",
    body: fd
  });

  const data = await r.json();

  if (data.status === "ok") {
    document.getElementById("modalExito").classList.remove("hidden");
    document.getElementById("modalExito").classList.add("flex");
  } else {
    alert("Error al crear el programa");
  }
};

function redirigirProgramas() {
  location.href = "/pages/programas.php";
}
</script>
