<?php

require '../logica/validar.php';
require '../logica/conexionbdd.php';

session_start();

if (!isset($_SESSION['id'])) {
    header('location:../login-sesion/login.php?error_message=Por favor inicie sesión');
    exit();
} else {
    if ((time() - $_SESSION['time']) > 600) {
        session_unset();
        session_destroy();
        header('location:../login-sesion/login.php?error_message=La sesión ha expirado');
        exit();
    }
}

$_SESSION['time'] = time();

$stmt = $conn->prepare("SELECT * FROM administrador WHERE id_administrador = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$resultado = $stmt->get_result();
$fila = $resultado->fetch_assoc();

$sql = "SELECT COUNT(*) FROM productos;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $cantidad_productos = $row["COUNT(*)"];
} else {
    $cantidad_productos = 0;
}

$sql = "SELECT COUNT(*) FROM productos WHERE stock_producto = 0;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $no_productos = $row["COUNT(*)"];
} else {
    $no_productos = 0;
}

// Obtener la cantidad de usuarios activos
$sql = "SELECT COUNT(*) FROM clientes WHERE estado_cliente = 'Activo';";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $usuarios_activos = $row["COUNT(*)"];
} else {
    $usuarios_activos = 0;
}

// Obtener la cantidad de órdenes pendientes
$sql = "SELECT COUNT(*) FROM ordenes WHERE estado = 'pendiente'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $ordenes_pendientes = $row["COUNT(*)"];
} else {
    $ordenes_pendientes = 0;
}

?>

<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">

    <title>Panel de Administración - Autorepuestos TirameAlgo</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">
</head>

<body class="bg-pattern transition-colors duration-200">
    <!-- Navbar -->
    <nav class="bg-custom-wineDeep backdrop-blur-sm dark:bg-custom-wineDeep text-custom-silver px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <div class="text-xl font-bold">Panel de Administración</div>
            <div class="flex items-center gap-4">
                <span class="text-sm bg-custom-steelDark px-3 py-1 rounded-full">Bienvenido, <?php echo $fila['nombre_administrador'] ?></span>
                <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-custom-wineDark dark:bg-custom-red hover:bg-custom-red dark:hover:bg-custom-wineDark transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                >
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="fixed left-0 top-16 h-full w-64 bg-custom-gray backdrop-blur-sm dark:bg-custom-steelDark shadow-lg">
        <div class="p-4">
            <nav class="space-y-2">
                <a class="block px-4 py-2 rounded-lg bg-custom-blue text-white hover:bg-custom-blue-light transition-colors">
                    Dashboard
                </a>
                <div class="space-y-1">
                    <div class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-white">Productos</div>
                    <a href="./ver-producto.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors dark:text-gray-400">
                        Ver Productos
                    </a>
                    <a href="./agregar-producto.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Agregar Producto
                    </a>
                </div>
                <div class="space-y-1">
                    <div class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-white">Clientes</div>
                    <a href="verClientes.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Clientes
                    </a>
                    <a href="./agregarCliente.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Agregar Clientes
                    </a>
                </div>
                <div class="space-y-1">
                    <div class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-white">Reportes</div>
                    <a href="./reportes.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Reporte de Ventas
                    </a>
                    <a href="./ordenes.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Órdenes
                    </a>
                </div>
                <div class="space-y-1">
                    <div class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-white">Estadisticas</div>

                    <a href="./estadisticas.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-400 transition-colors">
                        Ver
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <!-- Contenido Principal -->
    <main class="ml-64 pt-24 px-6 pb-20">
        <!-- Panel de Control -->
        <!-- TARJETA PRODUCTOS TOTALES -->

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

            <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6">
                <a>
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-400 text-sm">Productos Registrados</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white">
                                <?php echo $cantidad_productos; ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            <!-- TARJETA USUARIOS ACTIVOS -->
            <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6">
                <a>
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-400 text-sm">Usuarios Habilitados</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $usuarios_activos; ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <!-- TARJETA PRODUCTOS SIN STOCK -->
            <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6">
                <a>
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-400 text-sm">Productos sin Stock</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $no_productos; ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <!-- TARJETA ORDENES PENDIENTES -->
            <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6">
                <a>
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 dark:text-gray-400 text-sm">Órdenes pendientes</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-white"><?php echo $ordenes_pendientes; ?></p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tabla de Productos -->
        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Productos Recientes</h3>
                    <a>
                        <button class="px-4 py-2 bg-custom-blue hover:bg-custom-blue-light text-white rounded-md transition-colors duration-200">
                            Agregar Producto
                        </button>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-custom-silverLight dark:bg-custom-steelDark">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Producto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Categoría
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Stock
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-custom-silverLight dark:bg-custom-steelDark divide-y divide-gray-200 dark:divide-gray-700">

                        <?php
                        $sql_productos = "SELECT * FROM productos ORDER BY fecha_creacion DESC, hora_creacion DESC LIMIT 4;";
                        $result_productos = $conn->query($sql_productos);

                        if ($result_productos->num_rows > 0) {
                            while ($row = $result_productos->fetch_assoc()) {
                                $foto_productos = obtenerRutasArchivos($row['id_producto']);
                        ?>
                                <tr>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full object-cover" src="<?php echo $foto_productos; ?>" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <?php echo $row['nombre_producto']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white"><?php echo $row['categoria_producto']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">

                                        <?php
                                        if ($row['stock_producto'] > 5) {
                                        ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"> <?php echo $row['stock_producto']; ?></span>
                                        <?php
                                        } elseif ($row['stock_producto'] == 0) {
                                        ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"> <?php echo $row['stock_producto']; ?></span>

                                        <?php
                                        } elseif ($row['stock_producto'] < 6) {
                                        ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-00 dark:bg-yellow-700 dark:text-yellow-100"> <?php echo $row['stock_producto']; ?></span>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="editarProducto.php?numero_de_parte=<?php echo $row['numero_de_parte']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md transition-colors duration-200">
                                                Editar
                                            </a>
                                        </div>
                                    </td>
                            <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-6 py-4 text-center text-gray-500 dark:text-gray-400'>No hay productos recientes</td></tr>";
                        }
                            ?>
                                </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>

</html>