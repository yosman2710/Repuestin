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

// ----------------------------------------------------------------------
// Consulta SQL ÚNICA para obtener los datos necesarios para AMBAS GRÁFICAS
// Se obtienen Nombre, Precio y Cantidad Total Pedida.
// ----------------------------------------------------------------------
$sql = "SELECT p.nombre_producto, 
               p.precio_producto, 
               SUM(d.cantidad) as total_pedidos 
        FROM detalle_orden d 
        JOIN productos p ON d.id_producto = p.id_producto 
        GROUP BY p.id_producto, p.nombre_producto, p.precio_producto
        ORDER BY total_pedidos DESC 
        LIMIT 100"; // Límite amplio para la regresión

$result = $conn->query($sql);

$productos_raw = [];
$precios_raw = [];
$pedidos_raw = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos_raw[] = $row['nombre_producto'];
        $precios_raw[] = (float) $row['precio_producto']; 
        $pedidos_raw[] = (int) $row['total_pedidos'];
    }
}

// ----------------------------------------------------------------------
// Preparación de datos para la GRÁFICA DE BARRAS (Top 10)
// ----------------------------------------------------------------------
$productos_bar = array_slice($productos_raw, 0, 10);
$pedidos_bar = array_slice($pedidos_raw, 0, 10);

$productos_bar_json = json_encode($productos_bar);
$pedidos_bar_json = json_encode($pedidos_bar);

// ----------------------------------------------------------------------
// Preparación de datos para la GRÁFICA DE REGRESIÓN LINEAL (Todos los datos)
// ----------------------------------------------------------------------
$productos_reg_json = json_encode($productos_raw);
$precios_reg_json = json_encode($precios_raw);
$pedidos_reg_json = json_encode($pedidos_raw);

$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
    <title>Estadísticas - Autorepuestos TirameAlgo</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>

<body class="bg-pattern transition-colors duration-200">

    <nav class="bg-custom-wineDeep dark:bg-custom-wineDeep text-custom-silver px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
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

    <main class="pt-24 px-6 pb-20">
        
        <div class="bg-custom-red/95 backdrop-blur-sm dark:bg-custom-red-800/95 rounded-lg shadow-md overflow-hidden mb-6">
            <h2 class="w-full px-6 py-4 flex justify-between items-center text-left text-white dark:text-white text-lg font-semibold">
                Estadísticas de Productos
            </h2>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6">
            <h3 class="text-center py-2 text-xl font-bold dark:text-white">Top 10 Productos Más Solicitados 🥇</h3>
            <canvas id="chart" width="550" height="205"></canvas>
        </div>
        
        <hr class="my-8 border-t border-gray-300 dark:border-gray-700">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-6 mt-6">
            <h3 class="text-center py-2 text-xl font-bold dark:text-white">Regresión Lineal: Precio vs. Pedidos 📈</h3>
            <canvas id="regressionChart" width="550" height="205"></canvas>
        </div>
            
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>

        // ==========================================================
        // VARIABLES DE DATOS TRAÍDAS DESDE PHP
        // ==========================================================
        var productosBar = <?php echo $productos_bar_json; ?>; // Nombres (Top 10)
        var pedidosBar = <?php echo $pedidos_bar_json; ?>;     // Cantidades (Top 10)

        var productosReg = <?php echo $productos_reg_json; ?>; // Nombres (Todos)
        var preciosReg = <?php echo $precios_reg_json; ?>;     // Precios (Eje X, Todos)
        var pedidosReg = <?php echo $pedidos_reg_json; ?>;     // Cantidades (Eje Y, Todos)


        document.addEventListener('DOMContentLoaded', function() {

            // ==========================================================
            // GRÁFICA 1: BARRAS (Top 10 Productos Más Pedidos)
            // ==========================================================
            var ctx = document.getElementById('chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: productosBar,
                    datasets: [{
                        label: 'Productos más pedidos',
                        data: pedidosBar,
                        backgroundColor:[
                            'rgba(54, 162, 235, 0.9)',
                            'rgba(168, 40, 220, 0.9)',
                            'rgba(92, 229, 75, 0.9)',
                            'rgba(198, 75, 229 , 0.9)',
                            'rgba(255, 99, 132, 0.9)',
                            'rgba(255, 159, 64, 0.9)',
                            'rgba(75, 192, 192, 0.9)',
                            'rgba(153, 102, 255, 0.9)',
                            'rgba(201, 203, 207, 0.9)',
                            'rgba(50, 50, 50, 0.9)',
                        ],
                        borderColor: 'rgba(255, 255, 255, 0.5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });


            // ==========================================================
            // GRÁFICA 2: REGRESIÓN LINEAL (Precio vs. Pedidos)
            // ==========================================================

            /**
             * Calcula los parámetros de la regresión lineal (m y b).
             */
            function getLinearRegression(x, y) {
                var n = x.length;
                if (n === 0) return { m: 0, b: 0 };

                var sum_x = x.reduce((a, b) => a + b, 0);
                var sum_y = y.reduce((a, b) => a + b, 0);
                var sum_xy = x.map((xi, i) => xi * y[i]).reduce((a, b) => a + b, 0);
                var sum_x2 = x.map(xi => xi * xi).reduce((a, b) => a + b, 0);

                var denominator = n * sum_x2 - sum_x * sum_x;
                if (denominator === 0) return { m: 0, b: sum_y / n };

                var slope_m = (n * sum_xy - sum_x * sum_y) / denominator;
                var intercept_b = (sum_y - slope_m * sum_x) / n;

                return { m: slope_m, b: intercept_b };
            }

            // 1. Obtener los datos (X: Precio, Y: Pedidos)
            var x_data = preciosReg;
            var y_data = pedidosReg;

            // 2. Calcular la regresión
            var reg = getLinearRegression(x_data, y_data); 

            // 3. Generar los puntos de los datos reales (Scatter Plot)
            var scatterPoints = x_data.map((x, i) => ({
                x: x,
                y: y_data[i]
            }));
            
            // 4. Generar los puntos de la línea de regresión (Y = mX + b)
            var minPrice = Math.min(...x_data);
            var maxPrice = Math.max(...x_data);

            var regressionPoints = [
                { x: minPrice, y: reg.m * minPrice + reg.b },
                { x: maxPrice, y: reg.m * maxPrice + reg.b }
            ];

            // 5. Dibujar el gráfico
            var ctxReg = document.getElementById('regressionChart').getContext('2d');
            var regressionChart = new Chart(ctxReg, {
                type: 'scatter', 
                data: {
                    datasets: [
                        {
                            label: 'Pedidos por Producto (Puntos Reales)',
                            data: scatterPoints,
                            backgroundColor: 'rgba(54, 162, 235, 1)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            pointRadius: 5,
                            type: 'scatter'
                        },
                        {
                            label: `Línea de Regresión: y = ${reg.m.toFixed(2)}x + ${reg.b.toFixed(2)}`,
                            data: regressionPoints,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.5)',
                            fill: false,
                            pointRadius: 0, 
                            type: 'line',
                            borderDash: [5, 5] 
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Regresión Lineal: Precio del Producto vs. Total de Pedidos',
                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'white' : 'black'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = '';
                                    if (context.datasetIndex === 0) {
                                        // Para los puntos reales
                                        label = `Producto: ${productosReg[context.dataIndex]} | Precio: $${context.parsed.x.toFixed(2)} | Pedidos: ${context.parsed.y}`;
                                    } else {
                                        // Para la línea de regresión
                                        label = `Predicción en $${context.parsed.x.toFixed(2)}: ${context.parsed.y.toFixed(2)} pedidos`;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Precio del Producto ($)',
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'white' : 'black'
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Total de Pedidos',
                                color: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'white' : 'black'
                            },
                            beginAtZero: true,
                        }
                    }
                }
            });
        });
    </script>

</body>
</html>