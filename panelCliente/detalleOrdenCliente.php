<?php
require '../logica/conexionbdd.php';
session_start();

if(!ISSET($_SESSION['id'])){
    header('location:../login-sesion/login.php?error_message=Por favor inicie sesión');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('location: ordenes.php');
    exit();
}

$orden_id = $_GET['id'];

// Fetch order details
$sql_orden = "SELECT o.id_orden, o.fecha_creacion, o.estado, c.nombre_empresa,
                     SUM(d.cantidad * d.precio_unitario) as total
            FROM ordenes o
            INNER JOIN clientes c ON o.cliente_id = c.id
            INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
            WHERE o.id_orden = ?
            GROUP BY o.id_orden";

$stmt = $conn->prepare($sql_orden);
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$result_orden = $stmt->get_result();
$orden = $result_orden->fetch_assoc();

// Fetch order items
$sql_items = "SELECT p.nombre_producto as producto, d.cantidad, d.precio_unitario,
                     (d.cantidad * d.precio_unitario) as subtotal
            FROM detalle_orden d
            INNER JOIN productos p ON d.id_producto = p.id_producto
            WHERE d.id_orden = ?";

$stmt = $conn->prepare($sql_items);
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$result_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
    <title>Detalle de Orden - Autorepuestos TirameAlgo</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>
<body class="bg-pattern transition-colors duration-200">
    <!-- Navbar -->
    <nav class="bg-custom-wineDeep dark:bg-custom-wineDeep text-custom-silver px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <div class="text-xl font-bold">
                <a href="./cliente.php"
                class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm">Volver</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
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

    <!-- Contenido Principal -->
    <main class="pt-24 px-6 pb-20">
        <!-- Encabezado -->
        <div class="max-w-7xl mx-auto mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detalle de Orden</h1>
        </div>

        <!-- Detalle de la Orden -->
        <div class="max-w-7xl mx-auto bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Número de Orden:</h2>
                    <p class="text-gray-700 dark:text-gray-300">
                        <?php echo htmlspecialchars('ORD-' . str_pad($orden['id_orden'], 4, '0', STR_PAD_LEFT)); ?>
                    </p>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Fecha:</h2>
                    <p class="text-gray-700 dark:text-gray-300">
                        <?php echo date('d-m-Y', strtotime($orden['fecha_creacion'])); ?>
                    </p>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Empresa:</h2>
                    <p class="text-gray-700 dark:text-gray-300">
                        <?php echo htmlspecialchars($orden['nombre_empresa']); ?>
                    </p>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Estado:</h2>
                    <p class="<?php echo $orden['estado'] == 'pendiente' ? 'text-yellow-800 dark:text-yellow-200' : 'text-green-800 dark:text-green-200'; ?>">
                        <?php echo ucfirst($orden['estado']); ?>
                    </p>
                </div>
            </div>

            <!-- Order Items Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-custom-gray">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio Unitario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-custom-silverLight dark:bg-custom-steelDark divide-y divide-gray-200 dark:divide-gray-700">
                        <?php while($item = $result_items->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($item['producto']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        <?php echo $item['cantidad']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        $<?php echo number_format($item['precio_unitario'], 2); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        $<?php echo number_format($item['subtotal'], 2); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <tr class="bg-gray-50 dark:bg-custom-gray">
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    $<?php echo number_format($orden['total'], 2); ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

</body>
</html>
