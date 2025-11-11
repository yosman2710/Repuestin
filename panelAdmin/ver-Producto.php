<?php

require("../logica/conexionbdd.php");

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

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$search_query = "";
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT * FROM productos WHERE nombre_producto LIKE '%$search_query%' ORDER BY nombre_producto ASC;";
} else {
    $sql = "SELECT * FROM productos WHERE stock_producto > 0 ORDER BY nombre_producto ASC;";
}

$result = $conn->query($sql);
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';


?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
    <title>Lista de Productos - Autorepuestos TirameAlgo</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>

<body class="bg-pattern transition-colors duration-200">

                  <!-- Alerta de éxito -->
    <?php if ($success_message): ?>
        <div id="alertaExito" class="fixed top-0 left-0 right-0 z-50 transform -translate-y-full transition-transform duration-300 ease-in-out">
            <div class="max-w-4xl mx-auto mt-20 p-4 rounded-md bg-green-50 dark:bg-green-900 border border-green-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <!-- Ícono de éxito -->
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.707-5.707a1 1 0 011.414 0L10 12.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 dark:text-green-200"><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button onclick="cerrarAlertaExito()" class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 dark:hover:bg-green-800 transition-colors duration-200">
                                <span class="sr-only">Cerrar</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <!-- Navbar -->
    <nav class="bg-custom-wineDeep dark:bg-custom-wineDeep text-custom-silver px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="admin.php"
            class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
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

    <!-- Contenido Princpal -->
    <main class="pt-24 px-6 pb-20">
        


        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="ver-Producto.php" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label class="block text-sm font-medium text-custom-black dark:text-custom-silver mb-1">
                        Buscar producto
                    </label>
                    <input
                        type="text"
                        name="search"
                        placeholder="Nombre del producto..."
                        class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                            dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red"
                        value="<?php echo htmlspecialchars($search_query); ?>"
                    >
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange
                        dark:hover:bg-custom-red text-custom-silver rounded-md transition-colors duration-200">
                    Buscar
                </button>

                <?php if (!empty($search_query)): ?>
                    <a href="ver-Producto.php" class="w-full sm:w-auto px-4 py-2 bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange
                        dark:hover:bg-custom-red text-custom-silver rounded-md transition-colors duration-200">
                    Cancelar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Lista de Productos Sin Stock -->
        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md overflow-hidden mb-6">
            <button onclick="toggleSinStock()"
                    class="w-full px-6 py-4 flex justify-between items-center text-left text-custom-black dark:text-custom-silver hover:bg-custom-silverTitan dark:hover:bg-custom-gray">
                <div class="flex items-center">
                    <span class="text-lg font-semibold">Productos Agotados</span>
                </div>
                <svg id="arrow-sin-stock" class="w-5 h-5 transform rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div id="lista-sin-stock">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-custom-silverTitan dark:divide-custom-gray">
                        <thead class="bg-custom-gray-50 dark:bg-custom-gray">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Precio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider"></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Producto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-custom-silverLght dark:bg-custom-silverTitan divide-y divide-custom-silverTitan dark:divide-custom-gray" id="lista-sin-stock">

                        <?php
                                    $sql_2 = "SELECT * FROM productos WHERE stock_producto = 0 ORDER BY nombre_producto ASC;";
                                    $result_2 = $conn->query($sql_2);
                                    if ($result_2->num_rows > 0) {
                                        while ($row_2 = $result_2->fetch_assoc()) {

                        ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white w-24">
                                        <?php echo $row_2['numero_de_parte']; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $row_2['nombre_producto']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white"><?php echo $row_2['categoria_producto']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-white"><?php echo $row_2['precio_producto']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-red-600 dark:text-red-400 font-medium"><?php echo $row_2['stock_producto']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Sin Stock
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="editarProducto.php?numero_de_parte=<?php echo $row_2['numero_de_parte']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md transition-colors duration-200">
                                            Editar
                                        </a>
                                        <!--colocar accion -->
                                        <a href="../logica/eliminar-producto.php?id_producto=<?php echo $row_2['numero_de_parte']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md transition-colors duration-200" onclick="openModal(event)">
                                            Eliminar
                                        </a>
                                    </div>
                                    <!-- Modal -->
                                    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirmar Eliminación</h2>
                                            <p class="text-gray-700 dark:text-gray-300 mb-6">¿Estás seguro de que deseas eliminar este producto?</p>
                                            <div class="flex justify-end space-x-4">
                                                <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200">Cancelar</button>
                                                <a id="deleteLink" href="" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition-colors duration-200">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <?php
                                        } // Cierre del while
                                    } // Cierre del if
                                    ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lista de Productos Activos -->
        <div class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md overflow-hidden" id="lista-productos">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Productos Disponibles</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-custom-silverTitan dark:divide-custom-gray">
                    <thead class="bg-custom-gray-50 dark:bg-custom-gray">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-custom-gray dark:text-custom-silverLight uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-custom-silverLight dark:bg-custom-steelDark divide-y divide-custom-silverTitan dark:divide-custom-gray">
                        <?php

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {

                        ?>

                        <tr>
                        <a href="../catalogo/producto-detalle.php? echo $row['id_producto']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap" >
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white w-24">
                                    <?php echo $row['numero_de_parte']; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo $row['nombre_producto']; ?> </div>

                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white"><?php echo $row['categoria_producto']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white"><?php echo $row['precio_producto']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white"><?php echo $row['stock_producto']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">

                                <?php
                                if ($row['stock_producto'] > 5 ){


                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Disponible</span>';

                                }

                                if ($row['stock_producto'] < 6 ){


                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-500 text-yellow-800 dark:bg-yellow -900 dark:text-yellow-900">Poca existencia</span>';

                                }

                                ?>

                                </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                        <a href="editarProducto.php?numero_de_parte=<?php echo $row['numero_de_parte']; ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md transition-colors duration-200">
                                            Editar
                                        </a>
                                        <!--colocar accion con numero de parte-->
                                        <a href="../logica/eliminar-producto.php?id_producto=<?php echo $row['numero_de_parte']; ?>" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md transition-colors duration-200" onclick="openModal(event)">
                                            Eliminar
                                        </a>
                                    </div>
                                    <!-- Modal -->
                                    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
                                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Confirmar Eliminación</h2>
                                            <p class="text-gray-700 dark:text-gray-300 mb-6">¿Estás seguro de que deseas eliminar este producto?</p>
                                            <div class="flex justify-end space-x-4">
                                                <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors duration-200">Cancelar</button>
                                                <a id="deleteLink" href="" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition-colors duration-200">Eliminar</a>
                                            </div>
                                        </div>
                                    </div>
                            </td>
                            </a>    
                        </tr>
                        <?php
                            } // Cierre del while
                        } // Cierre del if
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>

        function toggleSinStock() {
            const lista = document.getElementById('lista-sin-stock');
            const arrow = document.getElementById('arrow-sin-stock');
            if (lista.classList.contains('hidden')) {
                lista.classList.remove('hidden');
                arrow.classList.add('rotate-180');
            } else {
                lista.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        }
        // Función para abrir y cerrar la confirmacion de eliminacion
        function openModal(event) {
            event.preventDefault();
            const deleteLink = event.target.closest('a').getAttribute('href');
            document.getElementById('deleteLink').setAttribute('href', deleteLink + '&deleted=true');
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function cerrarAlertaExito() {
            const alertaExito = document.getElementById('alertaExito');
            alertaExito.classList.add('-translate-y-full');
        }

        <?php if ($success_message): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const alertaExito = document.getElementById('alertaExito');
        alertaExito.classList.remove('-translate-y-full');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
<?php endif; ?>
    </script>
</body>
</html>