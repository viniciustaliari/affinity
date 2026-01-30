<?php
$current = $_SERVER['REQUEST_URI'];
?>

<head>
    
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

        <a class="flex bg-gray-500 rounded-full items-center px-3 text-white hover:bg-blue-400
        <?php echo ($current === '/pages/configuracion.php') ? 'bg-blue-400' : '' ?>
        <?php echo ($current === '/pages/editarPrecios.php') ? 'bg-blue-400' : '' ?>
        <?php echo ($current === '/pages/configBascula.php') ? 'bg-blue-400' : '' ?>" 
        href="/pages/configuracion.php">
            <img 
                src="../assets/img/config.png" 
                alt="User Icon" 
                width="27"
                height="30"
            >
        </a>
    </nav>
</header>


