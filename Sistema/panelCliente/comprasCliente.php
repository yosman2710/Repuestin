<?php
require '../logica/conexionbdd.php';
session_start();



$cliente_id = $_SESSION['id'];

// Consulta SQL con placeholder (?)
$sql_aprobadas = "SELECT o.id_orden, o.fecha_creacion, o.estado,
                     SUM(d.cantidad * d.precio_unitario) AS total
                FROM ordenes o
                INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
                WHERE o.estado = 'aceptada' AND o.cliente_id = ?
                GROUP BY o.id_orden, o.fecha_creacion, o.estado
                ORDER BY o.fecha_creacion DESC";

// Prepara la consulta
$stmt = $conn->prepare($sql_aprobadas);
if (!$stmt) {
    die("Error al preparar la consulta: " . $conn->error);
}

// Asigna el valor al placeholder
$stmt->bind_param("i", $cliente_id);

// Ejecuta la consulta
$stmt->execute();
$result_aprobadas = $stmt->get_result();

// Cierra la consulta
$stmt->close();


// Fetch pending orders
$sql_pendientes = "SELECT o.id_orden, o.fecha_creacion, o.estado,
                     SUM(d.cantidad * d.precio_unitario) as total
                FROM ordenes o
                INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
                WHERE o.cliente_id = ? AND o.estado = 'pendiente'
                GROUP BY o.id_orden
                ORDER BY o.fecha_creacion DESC";

$stmt = $conn->prepare($sql_pendientes);
$stmt->bind_param("i", $_SESSION['cliente_id']);
$stmt->execute();
$result_pendientes = $stmt->get_result();

// Fetch rejected orders
$sql_rechazadas = "SELECT o.id_orden, o.fecha_creacion, o.estado,
                     SUM(d.cantidad * d.precio_unitario) as total
                FROM ordenes o
                INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
                WHERE o.cliente_id = ? AND o.estado = 'rechazada'
                GROUP BY o.id_orden
                ORDER BY o.fecha_creacion DESC";

$stmt = $conn->prepare($sql_rechazadas);
$stmt->bind_param("i", $_SESSION['cliente_id']);
$stmt->execute();
$result_rechazadas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras - Autorepuestos Johbri</title>
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
            <div class="text-xl font-bold">
                <a href="cliente.php" class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm">Volver</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
            <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                 >
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesionCliente.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 pt-20 pb-12">

        <!-- ordenes aprobadas -->
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Órdenes Aprobadas</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Orden #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result_aprobadas->num_rows > 0): ?>
                                <?php while($row = $result_aprobadas->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ORD-<?php echo str_pad($row['id_orden'], 4, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            $<?php echo number_format($row['total'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Aprobada
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="./detalleOrdenCliente.php?id=<?php echo $row['id_orden']; ?>"
                                            class="text-custom-blue hover:text-custom-blue-light">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay órdenes aprobadas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <!--fdfdsf
         ordenes pendientes
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Órdenes Pendientes</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Orden #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result_pendientes->num_rows > 0): ?>
                                <?php while($row = $result_pendientes->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ORD-<?php echo str_pad($row['id_orden'], 4, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            $<?php echo number_format($row['total'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pendiente
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="./detalleOrdenCliente.php?php echo $row['id_orden']; ?>"
                                            class="text-custom-blue hover:text-custom-blue-light">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay órdenes pendientes
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

         ordenes rechazadas
        <section class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Órdenes Rechazadas</h2>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Orden #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result_rechazadas->num_rows > 0): ?>
                                <?php while($row = $result_rechazadas->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            ORD-<?php echo str_pad($row['id_orden'], 4, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            <?php echo date('d/m/Y', strtotime($row['fecha_creacion'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            $<?php echo number_format($row['total'], 2); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Rechazada
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="./detalleOrdenCliente.php?php echo $row['id_orden']; ?>"
                                               class="text-custom-blue hover:text-custom-blue-light">
                                                Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay órdenes rechazadas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    -->
    </main>

    <footer class="bg-custom-blue dark:bg-gray-800 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos Johbri, C.A. - Todos los derechos reservados</p>
    </footer>
</body>
</html>