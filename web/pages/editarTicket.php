<?php 
include_once '../components/head.php';
?>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl 
                grid grid-rows-8 grid-cols-7 gap-5 h-[80vh]">

        <!-- Fila 1: Botón Inicio + Título -->
        <div class="col-span-7 grid grid-cols-7 items-center">

            <!-- Botón Inicio -->
            <div class="col-span-1 flex justify-start">
                <a href="/pages/tickets.php"
                   class="bg-white text-blue-600 font-bold text-lg px-4 py-2 rounded-xl
                          border-2 border-blue-400 shadow hover:bg-blue-600 hover:text-white
                          transition cursor-pointer">
                    ← Tickets
                </a>
            </div>

          

        </div>

        <!-- Contenido principal -->
        <div class="col-span-5 row-span-6 col-start-2 flex gap-6 items-center justify-between font-bold text-xl">

            <form id="formTicket" 
                  class="flex flex-col gap-5 items-center justify-center w-full bg-white/20 p-6 rounded-xl backdrop-blur">

                <label class="text-white text-2xl underline">Cabecera</label>

                <input name="cabecera[]" type="text" placeholder="LÍNEA 1" class="ticket-line">
                <input name="cabecera[]" type="text" placeholder="LÍNEA 2" class="ticket-line">
                <input name="cabecera[]" type="text" placeholder="LÍNEA 3" class="ticket-line">
                <input name="cabecera[]" type="text" placeholder="LÍNEA 4" class="ticket-line">
                <input name="cabecera[]" type="text" placeholder="LÍNEA 5" class="ticket-line">

                <label class="text-white text-2xl underline mt-4">Pie</label>

                <input name="pie[]" type="text" placeholder="LÍNEA 1" class="ticket-line">
                <input name="pie[]" type="text" placeholder="LÍNEA 2" class="ticket-line">

                <button class="bg-green-500 p-3 rounded-lg cursor-pointer w-3/4 
                               hover:bg-green-600 hover:text-white shadow-lg transition text-white">
                    Actualizar ticket
                </button>

            </form>

        </div>

    </div>
</main>

<!-- Modal de éxito -->
<div id="modal-overlay"
     class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden">

    <div class="bg-white rounded-xl p-8 shadow-2xl w-96 text-center animate-fadeIn">
        <h2 class="text-2xl font-semibold text-green-600 mb-4">Guardado correctamente</h2>
        <p class="text-gray-700 mb-6">El ticket se ha actualizado correctamente.</p>

        <button onclick="closeModal()"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
            OK
        </button>
    </div>

</div>

<style>
.ticket-line {
    padding: 10px;
    border-radius: 10px;
    text-align: center;
    background: white;
    width: 100%;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to   { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
    animation: fadeIn 0.25s ease-out;
}
</style>

<script>
async function cargarTexto() {
    const res = await fetch('../api/getTexto.controller.php');
    const data = await res.json();

    const cab = document.querySelectorAll('input[name="cabecera[]"]');
    cab.forEach((input, i) => input.value = data.cabecera[i] || '');

    const pie = document.querySelectorAll('input[name="pie[]"]');
    pie.forEach((input, i) => input.value = data.pie[i] || '');
}

function openModal() {
    const m = document.getElementById('modal-overlay');
    m.classList.remove('hidden');
    m.classList.add('flex');
}

function closeModal() {
    const m = document.getElementById('modal-overlay');
    m.classList.add('opacity-0', 'transition', 'duration-300');
    setTimeout(() => {
        m.classList.add('hidden');
        m.classList.remove('flex', 'opacity-0', 'transition', 'duration-300');
    }, 300);
}

document.getElementById("formTicket").addEventListener("submit", async (e) => {
    e.preventDefault();

    const fd = new FormData(e.target);

    const res = await fetch('../api/setTexto.controller.php', {
        method: 'POST',
        body: fd
    });

    const data = await res.json();

    if (data.status === "ok") openModal();
});

cargarTexto();
</script>
