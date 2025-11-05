<?php

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

require '../logica/conexionbdd.php';

if (isset($_GET['numero_de_parte'])) {
    $numero_de_parte = $_GET['numero_de_parte'];

    $_SESSION['e_num_part'] = $numero_de_parte;
    
    $stmt = $conn->prepare("SELECT * FROM productos WHERE numero_de_parte = ?");
    $stmt->bind_param("s", $numero_de_parte);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $producto = $result->fetch_assoc();
    } else {
        echo "Producto no encontrado.";
        exit();
    }
} else {
    echo "Número de parte no proporcionado.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Autorepuestos</title>
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
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
                <a href="ver-Producto.php"
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

        <div class="max-w-4xl mx-auto">

            <div id="errorAlert" class="hidden ml-3 flex justify-between items-center bg-red-100 dark:bg-red-700 p-4 rounded">
                <p class="text-sm text-red-500 dark:text-red-100">
                    <?php
                    if (isset($_GET['error_message'])) {
                        echo urldecode($_GET['error_message']);
                    }
                    ?>
                </p>
                <button onclick="document.getElementById('errorAlert').classList.add('hidden')" class="text-red-500 dark:text-red-400">
                    &times;
                </button>
        </div>
            <br>
            <!-- Encabezado -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Producto</h1>
                <p class="text-gray-600 dark:text-gray-400">Modifica los detalles del producto según sea necesario</p>
            </div>

            <!-- Formulario -->
            <form action="../logica/actualizar-producto.php" method="POST" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <!-- Información básica -->
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Número de parte -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Número de parte
                            </label>
                            <input type="text" name="numero_de_parte_campo"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                                value="<?php echo $producto['numero_de_parte']; ?>">
                        </div>

                        <!-- Nombre del producto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre del producto
                            </label>
                            <input type="text" name="nombre_producto"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                                value="<?php echo $producto['nombre_producto']; ?>">
                        </div>

                        <!-- Precio -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Precio ($)
                            </label>
                            <input type="number" name="precio_producto"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                                value="<?php echo $producto['precio_producto']; ?>" step="0.01">
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Categoría
                            </label>
                            <select name="categoria_producto" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue">
                                <option <?php if ($producto['categoria_producto'] == 'Frenos') echo 'selected'; ?>>Frenos</option>
                                <option <?php if ($producto['categoria_producto'] == 'Suspensión') echo 'selected'; ?>>Suspensión</option>
                                <option <?php if ($producto['categoria_producto'] == 'Motor') echo 'selected'; ?>>Motor</option>
                                <option <?php if ($producto['categoria_producto'] == 'Transmisión') echo 'selected'; ?>>Transmisión</option>
                                <option <?php if ($producto['categoria_producto'] == 'Electricidad') echo 'selected'; ?>>Electricidad</option>
                                <option <?php if ($producto['categoria_producto'] == 'Carrocería') echo 'selected'; ?>>Carrocería</option>
                            </select>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Marca
                            </label>
                            <select name="marca_producto" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue">
                                <option <?php if ($producto['marca_producto'] == 'Toyota') echo 'selected'; ?>>Toyota</option>
                                <option <?php if ($producto['marca_producto'] == 'Honda') echo 'selected'; ?>>Honda</option>
                                <option <?php if ($producto['marca_producto'] == 'Chevrolet') echo 'selected'; ?>>Chevrolet</option>
                                <option <?php if ($producto['marca_producto'] == 'Ford') echo 'selected'; ?>>Ford</option>
                                <option <?php if ($producto['marca_producto'] == 'Nissan') echo 'selected'; ?>>Nissan</option>
                            </select>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Stock disponible
                            </label>
                            <input type="number" name="stock_producto"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                    dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                                value="<?php echo $producto['stock_producto']; ?>">
                        </div>

                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripción del producto
                        </label>
                        <textarea name="descripcion_producto"
                            class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600
                                dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue"
                            rows="4"><?php echo $producto['descripcion_producto']; ?></textarea>
                    </div>

                </div>

                <!-- Botones de acción -->
                <div class="mt-6 flex justify-end space-x-4">
                    <button onclick="history.back()" type="button" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 dark:text-gray-300
                            hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 bg-custom-blue hover:bg-custom-blue-light text-white rounded-md
                            transition-colors duration-200">
                        Guardar Cambios
                    </button>
                </div>
                <input type="hidden" name="numero_de_parte" value="<?php echo $producto['numero_de_parte']; ?>">
            </form>
        </div>
    </main>

    <footer class="bg-custom-blue dark:bg-gray-800 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos, C.A. - Todos los derechos reservados</p>
    </footer>

        document.addEventListener('DOMContentLoaded', function() {

            const errorMessage = "<?php echo isset($_GET['error_message']) ? urldecode($_GET['error_message']) : ''; ?>";
            
            if (errorMessage) {
                document.getElementById('errorAlert').classList.remove('hidden');
            }

        });
    </script>
</body>
</html>