<?php

require '../logica/validar.php';
require '../logica/conexionbdd.php';

session_start();

if (!isset($_SESSION['id'])) {
    header('location:../login-sesion/loginCliente.php?error_message=Por favor inicie sesión');
    exit();
} else {
    if ((time() - $_SESSION['time']) > 600) {
        session_unset();
        session_destroy();
        header('location:../login-sesion/loginCliente.php?error_message=La sesión ha expirado');
        exit();
    }
}

$_SESSION['time'] = time();

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Base query
$product_query = "SELECT id_producto, numero_de_parte, nombre_producto, precio_producto, categoria_producto, stock_producto 
                FROM productos
                WHERE stock_producto > 0";

// Add search filter
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $product_query .= " AND (nombre_producto LIKE '%$search%' OR numero_de_parte LIKE '%$search%')";
}

// Add category/brand/availability/price filters
if (!empty($filter)) {
    switch($filter) {
        // Categories
        case 'Frenos':
        case 'Suspensión':
        case 'Motor':
        case 'Transmisión':
        case 'Electricidad':
        case 'Carrocería':
            $filter = $conn->real_escape_string($filter);
            $product_query .= " AND categoria_producto = '$filter'";
            break;

        // Stock filters
        case 'En stock':
            $product_query .= " AND stock_producto > 10";
            break;
        case 'Poco stock':
            $product_query .= " AND stock_producto <= 10 AND stock_producto > 0";
            break;
        case 'Sin stock':
            $product_query .= " AND stock_producto = 0";
            break;

        // Price ranges
        case 'Menor a $50':
            $product_query .= " AND precio_producto < 50";
            break;
        case '$50 - $100':
            $product_query .= " AND precio_producto >= 50 AND precio_producto <= 100";
            break;
        case '$100 - $200':
            $product_query .= " AND precio_producto > 100 AND precio_producto <= 200";
            break;
        case 'Mayor a $200':
            $product_query .= " AND precio_producto > 200";
            break;
    }
}

// Add sorting
if (!empty($sort)) {
    switch($sort) {
        case 'Precio: Menor a mayor':
            $product_query .= " ORDER BY precio_producto ASC";
            break;
        case 'Precio: Mayor a menor':
            $product_query .= " ORDER BY precio_producto DESC";
            break;
        case 'Nombre: A-Z':
            $product_query .= " ORDER BY nombre_producto ASC";
            break;
        case 'Nombre: Z-A':
            $product_query .= " ORDER BY nombre_producto DESC";
            break;
        case 'Mayor stock':
            $product_query .= " ORDER BY stock_producto DESC";
            break;
    }
}

$product_result = $conn->query($product_query);

?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorepuestos Johbri, C.A.</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>
<body class="bg-pattern transition-colors duration-200">
    <!-- Navbar -->
    <nav class="bg-custom-blue dark:bg-gray-800 text-white px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
        <a href="cliente.php"
                class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm">Volver</span>
            </a>

            <div class="text-xl font-bold">Autorepuestos Johbri, C.A.</div>
            <div class="flex items-center gap-4">
                <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                 >
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="pt-24 px-6 pb-20">
        <!-- Filtros -->
        <form method="GET" action="" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <div class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Buscar producto
                    </label>
                    <input
                        type="text"
                        name="search"
                        placeholder="Nombre del producto..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                            dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                    >
                </div>
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Filtrar por
                    </label>
                    <select name="filter" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue">
                        <option value="">Todas las categorías</option>
                        <optgroup label="Categorías">
                        <option value="">Seleccione una categoría</option>
                            <option <?php echo $filter === 'Frenos' ? 'selected' : ''; ?>>Frenos</option>
                            <option <?php echo $filter === 'Inyección' ? 'selected' : ''; ?>>Inyección</option>
                            <option <?php echo $filter === 'Estoperas' ? 'selected' : ''; ?>>Estoperas</option>
                            <option <?php echo $filter === 'Suspensión' ? 'selected' : ''; ?>>Suspensión</option>
                            <option <?php echo $filter === 'Motor' ? 'selected' : ''; ?>>Motor</option>
                            <option <?php echo $filter === 'Filtros' ? 'selected' : ''; ?>>Filtros</option>
                            <option <?php echo $filter === 'Carroceria' ? 'selected' : ''; ?>>Carroceria</option>
                            <option <?php echo $filter === 'Accesorios' ? 'selected' : ''; ?>>Accesorios</option>
                            <option <?php echo $filter === 'Transmisión' ? 'selected' : ''; ?>>Transmisión</option>
                            <option <?php echo $filter === 'Electricidad' ? 'selected' : ''; ?>>Electricidad</option>
                            <option <?php echo $filter === 'Otros' ? 'selected' : ''; ?>>Otros</option>
                        </optgroup>
                        <optgroup label="Disponibilidad">
                            <option <?php echo $filter === 'En stock' ? 'selected' : ''; ?>>En stock</option>
                            <option <?php echo $filter === 'Poco stock' ? 'selected' : ''; ?>>Poco stock</option>
                            <option <?php echo $filter === 'Sin stock' ? 'selected' : ''; ?>>Sin stock</option>
                        </optgroup>
                        <optgroup label="Precio">
                            <option <?php echo $filter === 'Menor a $50' ? 'selected' : ''; ?>>Menor a $50</option>
                            <option <?php echo $filter === '$50 - $100' ? 'selected' : ''; ?>>$50 - $100</option>
                            <option <?php echo $filter === '$100 - $200' ? 'selected' : ''; ?>>$100 - $200</option>
                            <option <?php echo $filter === 'Mayor a $200' ? 'selected' : ''; ?>>Mayor a $200</option>
                        </optgroup>
                    </select>
                </div>
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ordenar por
                    </label>
                    <select name="sort" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue">
                        <option value="">Sin ordenar</option>
                        <option <?php echo $sort === 'Precio: Menor a mayor' ? 'selected' : ''; ?>>Precio: Menor a mayor</option>
                        <option <?php echo $sort === 'Precio: Mayor a menor' ? 'selected' : ''; ?>>Precio: Mayor a menor</option>
                        <option <?php echo $sort === 'Nombre: A-Z' ? 'selected' : ''; ?>>Nombre: A-Z</option>
                        <option <?php echo $sort === 'Nombre: Z-A' ? 'selected' : ''; ?>>Nombre: Z-A</option>
                        <option <?php echo $sort === 'Mayor stock' ? 'selected' : ''; ?>>Mayor stock</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-custom-blue hover:bg-custom-blue-light dark:bg-blue-600
                            dark:hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                    Aplicar Filtros
                </button>
            </div>
        </form>

        <!-- Grid de Productos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6 px-4">
            <?php while ($product = $product_result->fetch_assoc()):
                $foto_productos = obtenerRutasArchivos($product['id_producto']);

                ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 w-full max-w-sm mx-auto">
                    <div class="relative">
                        <img src="<?php echo $foto_productos; ?>" alt="<?php echo htmlspecialchars($product['nombre_producto']); ?>" class="w-full h-48 object-cover">
                        <span class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs">
                            En Stock
                        </span>
                    </div>
                    <div class="p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1"> <?php echo htmlspecialchars($product['categoria_producto']); ?></div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                            <?php echo htmlspecialchars($product['nombre_producto']); ?>
                        </h3>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-2xl font-bold text-custom-blue dark:text-blue-400">$<?php echo htmlspecialchars($product['precio_producto']); ?></span>
                            <span class="text-sm text-gray-600 dark:text-gray-300">Stock: <?php echo htmlspecialchars($product['stock_producto']); ?></span>
                        </div>
                        <div>
                            <a href="producto-detalle.php?id=<?php echo htmlspecialchars($product['id_producto']); ?>"
                            class="block w-full text-center bg-custom-blue hover:bg-custom-blue-light dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors duration-200">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            <?php
        endwhile; ?>
        </div>
    </main>

    <footer class="bg-custom-blue dark:bg-gray-800 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos Johbri, C.A. - Todos los derechos reservados</p>
    </footer>

</body>
</html>