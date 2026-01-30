<?php 
include_once '../components/head.php';
?>

<!-- MODAL GUARDADO CORRECTAMENTE -->
<?php if (isset($_GET['saved'])): ?>
<div id="modal-overlay"
    class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">

    <div class="bg-white rounded-xl p-8 shadow-2xl w-96 text-center">
        <h2 class="text-2xl font-semibold text-green-600 mb-4">Guardado correctamente</h2>
        <p class="text-gray-700 mb-6">La promoción ha sido creada exitosamente.</p>

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


<?php if (isset($_GET['error'])): ?>
<div id="modal-error"
     class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">

    <div class="bg-white rounded-xl p-8 shadow-2xl w-96 text-center">
        <h2 class="text-2xl font-semibold text-red-600 mb-4">Error</h2>

        <?php 
        // Mensaje configurable según el error recibido
        $msg = "Ocurrió un error al procesar la operación.";

        switch ($_GET['error']) {
            case "faltan_datos":
                $msg = "Faltan datos obligatorios en el formulario.";
                break;
            case "method":
                $msg = "Método no permitido.";
                break;
            case "bd":
                $msg = "No se pudo registrar en la base de datos.";
                break;
        }
        ?>

        <p class="text-gray-700 mb-6"><?= $msg ?></p>

        <button onclick="closeErrorModal()"
                class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition">
            Cerrar
        </button>
    </div>

</div>

<script>
function closeErrorModal() {
    const m = document.getElementById('modal-error');
    m.classList.add('opacity-0', 'transition', 'duration-300');
    setTimeout(() => m.remove(), 300);
}
</script>
<?php endif; ?>

<main class="px-3 relative">

<div class="absolute top-10 left-10 z-40">
    <a href="/pages/clp.php"
       class="bg-white text-blue-600 font-bold text-md px-4 py-2 rounded-xl
              border-2 border-blue-400 shadow hover:bg-blue-600 hover:text-white">
        ← Inicio
    </a>
</div>
<div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-3 rounded-2xl flex h-[80vh] gap-10 justify-center items-center">

    <!-- FORMULARIO -->
    <form action="/api/crearPromocion.controller.php"
          method="POST"
          enctype="multipart/form-data"
          class="w-3/5 flex gap-20 ps-10">

        <!-- COLUMNA IZQUIERDA -->
        <div class="w-3/5">

            <!-- NOMBRE PROMOCIÓN -->
            <label class="text-lg font-bold text-white ps-3">NOMBRE DE LA PROMOCIÓN</label>
            <input type="text" name="nombre"
                   required
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <!-- NOMBRE PRODUCTO -->
            <label class="text-lg font-bold text-white ps-3 mt-5">NOMBRE DEL PRODUCTO</label>
            <input type="text" name="nombre_producto"
                   required
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <!-- FECHAS -->
            <label class="text-lg font-bold text-white ps-3 mt-5">FECHA DE INICIO</label>
            <input type="date" name="fecha_inicio"
                   required
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <label class="text-lg font-bold text-white ps-3 mt-5">FECHA DE FIN</label>
            <input type="date" name="fecha_fin"
                   required
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <!-- IMAGEN -->
            <label class="text-lg font-bold text-white ps-3 mt-5">IMAGEN</label>
            <input type="file" name="imagen" id="promoImagen" accept="image/*"
                   onchange="previewImagen(event)"
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl cursor-pointer">

            <!-- ESTADO -->
            <label class="flex items-center gap-3 mt-5 text-white font-bold">
                <input type="checkbox" name="estado" class="w-5 h-5">
                Activar promoción
            </label>

        </div>

        <!-- COLUMNA DERECHA -->
        <div class="w-2/5">

            <!-- FRECUENCIA CLIENTE -->
            <label class="text-lg font-bold text-white ps-3">CLIENTE %</label>
            <input type="number" name="frecuencia_cliente" step="0.1"
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <!-- FRECUENCIA NO CLIENTE -->
            <label class="text-lg font-bold text-white ps-3 mt-5">NO CLIENTE %</label>
            <input type="number" name="frecuencia_no_cliente" step="0.1"
                   class="bg-white p-3 rounded-lg font-bold border-2 border-orange-300 w-full text-center shadow-xl">

            <!-- BOTÓN GUARDAR -->
            <input type="submit"
                   value="Guardar Promoción"
                   class="shadow-xl bg-orange-400 text-white text-xl p-3 rounded-lg font-bold border-2 border-orange-300 w-full mt-10 h-[35%] hover:bg-orange-500 cursor-pointer">

        </div>

    </form>

    <!-- VISTA PREVIA -->
    <div class="flex flex-col items-center justify-center w-2/5 ms-10 bg-white rounded-2xl p-5 gap-5 h-[70vh] me-10">
        <img id="previewPromo" src="../assets/img/placeholder_promo.png"
             alt="Previsualización" width="350" height="150"
             class="mb-5 shadow-xl rounded-lg">

        <p class="text-gray-700 font-bold">Vista previa de la promoción</p>
    </div>

</div>
</main>

<script>
function previewImagen(event) {
    const img = document.getElementById('previewPromo');
    img.src = URL.createObjectURL(event.target.files[0]);
}
</script>
