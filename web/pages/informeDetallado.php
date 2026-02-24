<?php 
include_once '../components/head.php';

$ctx = $_GET['ctx'] ?? null;
if (!$ctx) die("Falta ctx");
?>

<main class="px-3">

    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-4 rounded-2xl
                grid grid-cols-7 gap-4 h-[80vh] text-sm overflow-y-auto">

        <!-- ░ HEADER: INICIO + TÍTULO ░ -->
        <div class="col-span-7 grid grid-cols-7 items-center">
            
            <!-- INICIO -->
            <div class="col-span-1 flex justify-start">
                <a href="/"
                   class="bg-white text-blue-600 font-bold text-md px-4 py-2 rounded-xl
                          border-2 border-blue-400 shadow hover:bg-blue-600 hover:text-white">
                    ← Inicio
                </a>
            </div>

            <!-- TITULO -->
            <div class="col-span-5 flex justify-center">
                <h3 id="titulo"
                    class="font-bold text-2xl text-white tracking-wide">
                    Cargando...
                </h3>
            </div>

        </div>


        <!-- ░ TOTAL HOY ░ -->
        <div class="col-span-3 bg-white rounded-xl p-4 shadow-md">
            <h3 class="font-bold text-lg text-blue-700">Total generado hoy</h3>
            <p id="totalHoy" class="font-bold text-4xl pt-1 text-center text-gray-800">--</p>

            <!-- Mini Chart: hoy vs ayer -->
            <div class="bg-gray-100 mt-3 rounded-lg p-2 h-36 flex items-center">
                <canvas id="chartHoyAyer"></canvas>
            </div>
        </div>


        <!-- ░ PROGRAMA ACTIVO ░ -->
        <div class="col-span-4 bg-white rounded-xl p-4 shadow-md">
            <h3 class="font-bold text-lg text-blue-700">Programa activo</h3>
            <p id="programaActivo" class="font-bold text-3xl pt-2 text-center text-gray-800">--</p>
        </div>


        <!-- ░ TOTAL MES ░ -->
        <div class="col-span-3 bg-white rounded-xl p-4 shadow-md">
            <h3 class="font-bold text-lg text-blue-700">Total este mes</h3>
            <p id="totalMes" class="font-bold text-4xl pt-1 text-center text-gray-800">--</p>
        </div>


        <!-- ░ COMPARACIÓN MES ANTERIOR (con chart) ░ -->
        <div class="col-span-4 row-span-2 bg-white rounded-xl p-4 shadow-md flex flex-col">

            <h3 class="font-bold text-lg text-blue-700">Comparación con mes anterior</h3>

            <p id="comparacion"
               class="text-center font-bold text-2xl mt-3 text-gray-800">
                --
            </p>

            <!-- Chart contenedor controlado -->
            <div class="mt-3 bg-gray-100 rounded-lg p-2 h-48 flex items-center justify-center">
                <canvas id="chartComparacion"></canvas>
            </div>
        </div>


        <!-- ░ CHART DIARIO (pequeño) ░ -->
        <div class="col-span-3 bg-white rounded-xl p-4 shadow-md">
            <h3 class="font-bold text-lg text-blue-700">Uso diario</h3>

            <div class="mt-2 bg-gray-100 rounded-lg p-2 h-40 flex items-center">
                <canvas id="chartDia"></canvas>
            </div>
        </div>

    </div>

</main>


<script>
const backLink = document.querySelector('.col-span-1 a[href="/"]');
if (backLink) {
    backLink.className =
        'z-20 w-16 h-16 rounded-full border-2 border-white/85 bg-white/25 ' +
        'flex items-center justify-center text-white hover:bg-orange-500 ' +
        'hover:border-orange-300 transition shadow-lg';
    backLink.setAttribute('aria-label', 'Volver atras');
    backLink.setAttribute('title', 'Volver atras');
    backLink.innerHTML =
        '<svg xmlns=\"http://www.w3.org/2000/svg\" class=\"w-9 h-9\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"3.5\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\">' +
        '<path d=\"M19 12H5\"></path>' +
        '<path d=\"M12 19L5 12L12 5\"></path>' +
        '</svg>';
}

const ctx = "<?= $ctx ?>";

// ======================
//   CARGAR INFORME API
// ======================
fetch(`/api/getInforme.controller.php?ctx=${ctx}`)
    .then(r => r.json())
    .then(d => {
        console.log(d);

        document.getElementById("titulo").innerText =
            "INFORME: " + d.servicio.nombre;

        document.getElementById("totalHoy").innerText =
            d.totalHoy.toFixed(2) + "€";

        document.getElementById("totalMes").innerText =
            d.totalMes.toFixed(2) + "€";

        document.getElementById("programaActivo").innerText =
            d.programaActivo ? d.programaActivo.nombre : "Ninguno";

        document.getElementById("comparacion").innerText =
            `${d.totalMesAnterior.toFixed(2)}€ → ${d.totalMes.toFixed(2)}€`;

        renderChartDia(d.datosDiarios);
        renderComparacion(d.totalMesAnterior, d.totalMes);
    });


// ======================
//   CHART DIARIO
// ======================
function renderChartDia(data) {
    const labels = data.map(x => x.dia);
    const values = data.map(x => x.conteo);

    new Chart(document.getElementById("chartDia"), {
        type: "line",
        data: {
            labels,
            datasets: [{
                label: "Usos",
                data: values,
                borderColor: "#1d4ed8",
                backgroundColor: "rgba(29,78,216,0.2)",
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}


// ======================
//   CHART MES ACTUAL vs ANTERIOR
// ======================
function renderComparacion(mesAnterior, mesActual) {
    new Chart(document.getElementById("chartComparacion"), {
        type: "bar",
        data: {
            labels: ["Mes anterior", "Mes actual"],
            datasets: [{
                data: [mesAnterior, mesActual],
                backgroundColor: ["#f97316", "#22c55e"]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}


// ======================
//   CHART HOY vs AYER
// ======================
fetch("/api/getComparacionHoyAyer.controller.php")
    .then(r => r.json())
    .then(d => {
        new Chart(document.getElementById("chartHoyAyer"), {
            type: "bar",
            data: {
                labels: ["Ayer", "Hoy"],
                datasets: [{
                    data: [d.ayer, d.hoy],
                    backgroundColor: ["#fb7185", "#4ade80"]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
