<?php
$current = $_SERVER['REQUEST_URI'];
$currentPath = parse_url($current, PHP_URL_PATH) ?? '/';
$isConfigArea =
    str_contains($currentPath, '/pages/configuracion') ||
    str_contains($currentPath, '/pages/configBascula') ||
    str_contains($currentPath, '/pages/editarPrecios') ||
    str_contains($currentPath, '/pages/configServidor');
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/head.css">
    <link rel="stylesheet" href="../output.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<header class="p-3 flex justify-between">
    <a href="/">
        <img 
            src="../assets/img/logo.png" 
            alt="Affinity Logo"
            width="100"
            height="50"
        >
    </a>

    <nav class="flex">
        <ul class="flex gap-4 mr-4 bg-gray-500 rounded-full items-center p-1 justify-center">
            <li class="flex">
                <a class="flex items-center border-2 border-blue-400 justify-center px-6 py-2 rounded-full text-white hover:bg-blue-400
                <?php echo ($current === '/') ? 'bg-blue-400' : '' ?>" 
                href="/">INICIO</a>
            </li>

            <li class="flex">
                <a class="flex items-center border-2 border-blue-400 justify-center px-6 py-2 rounded-full text-white hover:bg-blue-400
                <?php echo ($current === '/pages/tickets.php') ? 'bg-blue-400' : '' ?>
                <?php echo ($current === '/pages/editarTicket.php') ? 'bg-blue-400' : '' ?>" 
                href="/pages/tickets.php">TICKETS</a>
            </li>
            
            <?php
            $isEditarPrograma = str_contains($_SERVER['REQUEST_URI'], 'editarPrograma.php');
            ?>

            <li class="flex">
                <a class="flex items-center border-2 border-blue-400 justify-center px-6 py-2 rounded-full text-white hover:bg-blue-400
                <?php echo ($current === '/pages/programas.php') ? 'bg-blue-400' : '' ?>
                <?= $isEditarPrograma ? 'bg-blue-400 text-white' : '' ?>
                <?php echo ($current === '/pages/crearPrograma.php') ? 'bg-blue-400' : '' ?>" 
                href="/pages/programas.php">PROGRAMAS</a>
            </li>

            <?php
            $isEditarPromo = str_contains($_SERVER['REQUEST_URI'], 'editarPromocion.php');
            ?>

            <li class="flex">
                <a class="flex items-center border-2 border-blue-400 justify-center px-6 py-2 rounded-full text-white hover:bg-blue-400
                <?php echo ($current === '/pages/clp.php') ? 'bg-blue-400' : '' ?>
                <?php echo ($current === '/pages/nuevaPromocion.php') ? 'bg-blue-400' : '' ?>
                <?= $isEditarPromo ? 'bg-blue-400 text-white' : '' ?>" 
                href="/pages/clp.php">CLP</a>
            </li>

            <li class="flex">
                <a class="flex items-center border-2 border-blue-400 justify-center px-6 py-2 rounded-full text-white hover:bg-blue-400
                <?php echo ($current === '/pages/informacion.php') ? 'bg-blue-400' : '' ?>" 
                href="/pages/informacion.php">INFORMACIÓN</a>
            </li>
        </ul>

        <a class="flex rounded-full items-center px-3 text-white transition
        <?= $isConfigArea ? 'bg-blue-400 ring-2 ring-blue-300 ring-offset-2 ring-offset-transparent' : 'bg-gray-500 hover:bg-blue-400' ?>" 
        href="/pages/configuracion.php">
            <img 
                src="../assets/img/config.png" 
                alt="User Icon" 
                class="brightness-0 invert"
                style="filter: brightness(0) invert(1);"
                width="27"
                height="30"
            >
        </a>
    </nav>
</header>
