<?php include_once '../components/head.php'; ?>

<?php require __DIR__ . '/../api/getPrecios.controller.php'; ?>

<?php if (isset($_GET['saved'])): ?>
<div id="modal-overlay"
     class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">

    <div class="bg-white rounded-xl p-8 shadow-2xl w-96 text-center">
        <h2 class="text-2xl font-semibold text-green-600 mb-4">Guardado correctamente</h2>
        <p class="text-gray-700 mb-6">El precio se ha actualizado correctamente.</p>

        <button onclick="closeModal()"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition">
            OK
        </button>
    </div>

</div>

<script>
function closeModal() {
    const m = document.getElementById('modal-overlay');
    m.classList.add('opacity-0', 'transition', 'duration-300');
    setTimeout(() => m.remove(), 300);
}
</script>
<?php endif; ?>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl grid grid-rows-7 grid-cols-7 gap-5 h-[80vh]">

        <div class="col-span-7 grid grid-cols-3 items-center px-2">
            <div class="flex items-center justify-start">
                <a href="/pages/configuracion.php"
                   class="z-20 w-16 h-16 rounded-full border-2 border-white/85 bg-white/25 flex items-center justify-center text-white hover:bg-orange-500 hover:border-orange-300 transition shadow-lg"
                   aria-label="Volver atras"
                   title="Volver atras">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5"></path>
                        <path d="M12 19L5 12L12 5"></path>
                    </svg>
                </a>
            </div>
            <h3 class="font-bold text-xl text-white text-center">
                PRECIFICACION
            </h3>
            <div></div>
        </div>

        <!-- PESO/ALTURA (id = 1) -->
        <div class="col-span-3 col-start-3 flex gap-6 items-center justify-between font-bold text-xl">
            <p class="text-white">PESO/ALTURA</p>

            <form method="POST" action="../api/setPrecios.controller.php"
                  class="flex flex-row gap-5 items-center justify-center">

                <input type="text"
                       name="peso"
                       value="<?= $prices[1] ?>"
                       class="p-3 rounded-lg text-center bg-white w-1/4">

                <button class="bg-green-400 p-3 rounded-lg cursor-pointer w-2/4 hover:bg-green-500 hover:text-white shadow-xl/20">
                    Actualizar
                </button>

            </form>
        </div>

        <!-- BLOOD PRESSURE (id = 2) -->
        <div class="col-span-3 col-start-3 flex gap-6 items-center justify-between font-bold text-xl">
            <p class="text-white">BLOOD PRESSURE</p>

            <form method="POST" action="../api/setPrecios.controller.php"
                  class="flex flex-row gap-5 items-center justify-center">

                <input type="text"
                       name="bp"
                       value="<?= $prices[2] ?>"
                       class="p-3 rounded-lg text-center bg-white w-1/4">

                <button class="bg-green-400 p-3 rounded-lg cursor-pointer w-2/4 hover:bg-green-500 hover:text-white shadow-xl/20">
                    Actualizar
                </button>

            </form>
        </div>

        <!-- OXI (id = 3) -->
        <div class="col-span-3 col-start-3 flex gap-6 items-center justify-between font-bold text-xl">
            <p class="text-white">OXI</p>

            <form method="POST" action="../api/setPrecios.controller.php"
                  class="flex flex-row gap-5 items-center justify-center">

                <input type="text"
                       name="oxi"
                       value="<?= $prices[3] ?>"
                       class="p-3 rounded-lg text-center bg-white w-1/4">

                <button class="bg-green-400 p-3 rounded-lg cursor-pointer w-2/4 hover:bg-green-500 hover:text-white shadow-xl/20">
                    Actualizar
                </button>

            </form>
        </div>

        <!-- IMG (id = 4) -->
        <div class="col-span-3 col-start-3 flex gap-6 items-center justify-between font-bold text-xl">
            <p class="text-white">IMG</p>

            <form method="POST" action="../api/setPrecios.controller.php"
                  class="flex flex-row gap-5 items-center justify-center">

                <input type="text"
                       name="img"
                       value="<?= $prices[4] ?>"
                       class="p-3 rounded-lg text-center bg-white w-1/4">

                <button class="bg-green-400 p-3 rounded-lg cursor-pointer w-2/4 hover:bg-green-500 hover:text-white shadow-xl/20">
                    Actualizar
                </button>

            </form>
        </div>

        <!-- TODO (id = 5) -->
        <div class="col-span-3 col-start-3 flex gap-6 items-center justify-between font-bold text-xl">
            <p class="text-white">TODO</p>

            <form method="POST" action="../api/setPrecios.controller.php"
                  class="flex flex-row gap-5 items-center justify-center">

                <input type="text"
                       name="todo"
                       value="<?= $prices[5] ?>"
                       class="p-3 rounded-lg text-center bg-white w-1/4">

                <button class="bg-green-400 p-3 rounded-lg cursor-pointer w-2/4 hover:bg-green-500 hover:text-white shadow-xl/20">
                    Actualizar
                </button>

            </form>
        </div>

    </div>
</main>
