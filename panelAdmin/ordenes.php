<?php
require '../logica/conexionbdd.php';
session_start();

if(!ISSET($_SESSION['id'])){
    header('location:../login-sesion/login.php?error_message=Por favor inicie sesión');
    exit();
}

else{
    if((time() - $_SESSION['time']) > 600){
        session_unset();
        session_destroy();
        header('location:../login-sesion/login.php?error_message=La sesión ha expirado');
        exit();
    }
}

$_SESSION['time'] = time();

// obtener ordenes pendientes
$sql_pendientes = "SELECT o.id_orden, o.fecha_creacion, o.estado, c.nombre_empresa,
                     SUM(d.cantidad * d.precio_unitario) as total
                FROM ordenes o
                INNER JOIN clientes c ON o.cliente_id = c.id
                INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
                WHERE o.estado = 'pendiente'
                GROUP BY o.id_orden
                ORDER BY o.fecha_creacion DESC";
$result_pendientes = $conn->query($sql_pendientes);

// obtener ordenes aprobadas
$sql_aprobadas = "SELECT o.id_orden, o.fecha_creacion, o.estado, c.nombre_empresa,
                  SUM(d.cantidad * d.precio_unitario) as total
                FROM ordenes o
                INNER JOIN clientes c ON o.cliente_id = c.id
                INNER JOIN detalle_orden d ON o.id_orden = d.id_orden
                WHERE o.estado = 'aceptada'
                GROUP BY o.id_orden
                ORDER BY o.fecha_creacion DESC";
$result_aprobadas = $conn->query($sql_aprobadas);
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Órdenes Pendientes - Autorepuestos TirameAlgo</title>
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
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
                <a href="admin.php"
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
        <div class="max-w-7xl mx-auto mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Órdenes</h1>
        </div>

        <!-- Lista de Órdenes Pendientes -->
        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md overflow-hidden mb-6">
            <button onclick="togglePendientes()"
                    class="w-full px-6 py-4 flex justify-between items-center text-left text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                <div class="flex items-center">
                    <span class="text-lg font-semibold">Órdenes Pendientes</span>
                </div>
                <svg id="arrow-pendientes" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="lista-pendientes">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-custom-gray">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Número de Orden
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Empresa
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
                        <!-- ordenes pendientes-->
                        <tbody class="bg-custom-silverLight dark:bg-custom-steelDark divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result_pendientes->num_rows > 0): ?>
                                <?php while($row = $result_pendientes->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars('ORD-' . str_pad($row['id_orden'], 4, '0', STR_PAD_LEFT)); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($row['nombre_empresa']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo date('d-m-Y', strtotime($row['fecha_creacion'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                $<?php echo number_format($row['total'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pendiente
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="detalleOrden.php?id=<?php echo $row['id_orden']; ?>"
                                                class="text-custom-blue hover:text-custom-blue-light dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Ver detalles">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <button onclick="aprobarOrden(<?php echo $row['id_orden']; ?>)"
                                                        class="text-green-500 hover:text-green-600 dark:text-green-400 dark:hover:text-green-300"
                                                        title="Aprobar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                                <button onclick="rechazarOrden(<?php echo $row['id_orden']; ?>)"
                                                        class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Rechazar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay órdenes pendientes
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--ordenes Aprobadas -->
        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md overflow-hidden">
            <button onclick="toggleAprobadas()"
                    class="w-full px-6 py-4 flex justify-between items-center text-left text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                <div class="flex items-center">
                    <span class="text-lg font-semibold">Órdenes Aprobadas</span>
                </div>
                <svg id="arrow-aprobadas" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="lista-aprobadas">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-custom-gray">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Número de Orden
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Empresa
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

                        <!-- valores ordenes aprobadas-->
                        <tbody class="bg-custom-silverLight dark:bg-custom-steelDark divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result_aprobadas->num_rows > 0): ?>
                                <?php while($row = $result_aprobadas->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars('ORD-' . str_pad($row['id_orden'], 4, '0', STR_PAD_LEFT)); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($row['nombre_empresa']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <?php echo date('d-m-Y', strtotime($row['fecha_creacion'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                $<?php echo number_format($row['total'], 2); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Aprobada
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="detalleOrden.php?id=<?php echo $row['id_orden']; ?>"
                                                   class="text-custom-blue hover:text-custom-blue-light dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Ver detalles">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay órdenes aprobadas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

<script>
function aprobarOrden(id) {
    if (confirm('¿Está seguro de que desea aprobar esta orden?')) {
        window.location.href = `../logica/aprobar-orden.php?id=${id}`;
    }
}
function rechazarOrden(id) {
    if (confirm('¿Está seguro de que desea rechazar esta orden?')) {
        window.location.href = `../logica/rechazar-orden.php?id=${id}`;
    }
}

function toggleAprobadas() {
    var listaAprobadas = document.getElementById('lista-aprobadas');
    var arrowAprobadas = document.getElementById('arrow-aprobadas');
    listaAprobadas.classList.toggle('hidden');
    arrowAprobadas.classList.toggle('rotate-180');
}

function togglePendientes(){
    var listaPendientes = document.getElementById('lista-pendientes');
    var arrowPendientes = document.getElementById('arrow-pendientes');
    listaPendientes.classList.toggle('hidden');
    arrowPendientes.classList.toggle('rotate-180');
}
</script>
</body>
</html>