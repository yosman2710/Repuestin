<?php
require("../logica/conexionbdd.php");
session_start();

if (!isset($_SESSION['id'])) {
    header('location:../login-sesion/login.php?error_message=Por favor inicie sesión');
    exit();
}

if ((time() - $_SESSION['time']) > 600) {
    session_unset();
    session_destroy();
    header('location:../login-sesion/login.php?error_message=La sesión ha expirado');
    exit();
}

$_SESSION['time'] = time();

if (!isset($conn) || $conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los productos más pedidos desde la tabla detalle_orden
$sql = "SELECT p.nombre_producto, SUM(d.cantidad) as total_pedidos 
        FROM detalle_orden d 
        JOIN productos p ON d.id_producto = p.id_producto 
        GROUP BY p.nombre_producto 
        ORDER BY total_pedidos DESC 
        LIMIT 10";
$result = $conn->query($sql);

$productos = [];
$pedidos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row['nombre_producto'];
        $pedidos[] = $row['total_pedidos'];
    }
}

$productos_json = json_encode($productos);
$pedidos_json = json_encode($pedidos);

$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
    <title>Lista de Productos - Autorepuestos Johbri</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>

<body class="bg-pattern transition-colors duration-200">

    <!-- Navbar -->
    <nav class="bg-custom-blue dark:bg-gray-800 text-white px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="admin.php" class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-sm">Volver</span>
            </a>
            <div class="flex items-center gap-4">
            <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                 >
                    <span id="theme-icon">🌙</span>
                </button>
                <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="pt-24 px-6 pb-20">
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6">
            <button class="w-full px-6 py-4 flex justify-between items-center text-left text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                <span class="text-lg font-semibold">Estadísticas</span>
            </button>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6">
            <canvas id="chart" width="550" height="205"></canvas>
        </div>
           
    </main>

    <footer class="bg-custom-blue dark:bg-gray-800 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos Johbri, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>

        // Datos de productos y pedidos desde PHP
        var productos = <?php echo $productos_json; ?>;
        var pedidos = <?php echo $pedidos_json; ?>;

        // Cargar Chart.js correctamente
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: productos,
                    datasets: [{
                        label: 'Productos más pedidos',
                        data: pedidos,
                        backgroundColor:[
                            'rgba(54, 162, 235, 0.9)',
                            'rgba(168, 40, 220, 0.9)',
                            'rgba(92, 229, 75, 0.9)',
                            'rgba(198, 75, 229 , 0.9)',

                        ],
                        borderColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>

</body>
</html>
