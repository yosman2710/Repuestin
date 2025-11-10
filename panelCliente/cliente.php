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

$client_id = $_SESSION['id'];
$query = "SELECT nombre_empresa, nombre_encargado, rif FROM clientes WHERE id = ?";

// consulta para productos aleatorios de la bdd
$sql_productos = "SELECT * FROM productos where stock_producto > 0 ORDER BY RAND() LIMIT 4;";
$result_productos = $conn->query($sql_productos);

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Cliente - Autorepuestos TirameAlgo </title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">
    
    <style>
        /* Estilos para el carrusel de productos */
        .product-carousel {
            position: relative;
            overflow: hidden;
            border-radius: 0.5rem;
        }

        .product-carousel-track {
            display: flex;
            transition: transform 0.5s ease-out;
        }

        .product-slide {
            flex: 0 0 100%;
            position: relative;
        }

        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .product-content {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-details {
            padding: 1.5rem;
            background-color: custom-gray;
            color: black;
            flex-grow: 1;
        }

        .dark .product-details {
            background-color: #1f2937;
            color: white;
        }

        .carousel-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border-radius: 9999px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .carousel-button:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-button.prev {
            left: 1rem;
        }

        .carousel-button.next {
            right: 1rem;
        }

        .carousel-indicators {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 10;
        }

        .carousel-indicator {
            width: 10px;
            height: 10px;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dark .carousel-indicator {
            background: rgba(255, 255, 255, 0.3);
        }

        .carousel-indicator.active {
            width: 30px;
            background: #2563eb;
        }

        .carousel-indicator:hover {
            background: rgba(37, 99, 235, 0.8);
        }
    </style>
</head>
<body class="bg-pattern transition-colors duration-200">
    <!-- Navbar -->
    <nav class="bg-custom-wineDeep dark:bg-custom-wineDeep text-custom-silver backdrop-blur-sm px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">

        <div class="text-xl font-bold">Autorepuestos TirameAlgo, C.A.</div>
            <div class="text-xl font-bold"> <?php echo htmlspecialchars($client_data['nombre_empresa']) . "     " . htmlspecialchars($client_data['rif']); ?></div>
            <div class="flex items-center gap-4">
                <span class="text-sm bg-custom-steelDark px-3 py-1 rounded-full">Bienvenido, <?php echo htmlspecialchars($client_data['nombre_encargado']); ?></span>
                <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-custom-wineDark dark:bg-custom-red hover:bg-custom-red dark:hover:bg-custom-wineDark transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                >
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesionCliente.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <!-- Sidebar -->
    <div class="fixed left-0 top-16 h-full w-64 bg-custom-gray backdrop-blur-sm dark:bg-custom-steelDark shadow-lg">
        <div class="p-4">
            <nav class="space-y-2">
                <a class="block px-4 py-2 rounded-lg text-custom-silverLight  transition-colors">
                    Dashboard
                </a>
                <div class="space-y-1">
                    <div class="px-4 py-2 text-sm font-semibold text-custom-steelDark dark:text-custom-silver">Mi Cuenta</div>
                    <a href="./comprasCliente.php" class="block px-4 py-2 rounded-lg hover:bg-custom-steelDark dark:hover:bg-custom-gray hover:text-custom-silver dark:hover:text-custom-black transition-colors dark:text-gray-400">
                        Mis Compras
                    </a>
                    <a href="./catalogo.php" class="block px-4 py-2 rounded-lg hover:bg-custom-steelDark dark:hover:bg-custom-gray hover:text-custom-silver dark:hover:text-custom-black transition-colors dark:text-gray-400">
                        Catálogo de Productos
                    </a>
                    <a href="./carrito.php" class="flex px-4 py-2 rounded-lg hover:bg-custom-steelDark dark:hover:bg-custom-gray hover:text-custom-silver dark:hover:text-custom-black dark:text-gray-400 transition-colors items-center justify-between">
                        <span>Carrito de Compras</span>
                        <span id="cart-count" class="bg-custom-blue text-white text-xs px-2 py-1 rounded-full"></span>
                    </a>
                    <a href="./datosCliente.php" class="block px-4 py-2 rounded-lg hover:bg-custom-steelDark dark:hover:bg-custom-gray hover:text-custom-silver dark:hover:text-custom-black dark:text-gray-400 transition-colors">
                        Mis Datos
                    </a>
                    <!-- Nueva opción de Repuestos -->
                    <a href="./repuestin.php" class="block px-4 py-2 rounded-lg hover:bg-custom-steelDark dark:hover:bg-custom-gray hover:text-custom-silver dark:hover:text-custom-black dark:text-gray-400 transition-colors">
                        Repuestin
                    </a>
                </div>
            </nav>
        </div>
    </div>

    <!-- Contenido Principal -->
    <main class="ml-64 pt-24 px-6 pb-20">
        <!-- Carrusel de Productos -->
        <div class="relative overflow-hidden rounded-lg shadow-lg">
            <!-- Carrusel Track -->
            <div id="carousel-track" class="flex h-[500px] transition-transform duration-500 ease-in-out">
                <?php
                if ($result_productos->num_rows > 0) {
                    while ($row = $result_productos->fetch_assoc()) {
                        $foto_productos = obtenerRutasArchivos($row['id_producto']);
                ?>
                <!-- Producto -->
                <div class="w-full flex-shrink-0">
                    <div class="flex flex-col md:flex-row h-full">
                        <!-- Imagen del Producto -->
                        <div class="relative w-full md:w-1/2 h-64 md:h-full">
                            <img src="<?php echo $foto_productos; ?>" alt="<?php echo $row['nombre_producto']; ?>" class="w-full h-full object-cover">
                        </div>

                        <!-- Detalles del Producto -->
                        <div class="w-full md:w-1/2 p-6 md:p-8 bg-custom-gray dark:bg-custom-steelDark flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo $row['nombre_producto']; ?></h3>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    Código: <?php echo $row['numero_de_parte']; ?>
                                </span>
                            </div>

                            <div class="mb-2 text-sm font-medium text-custom-black dark:text-custom-silverLight">
                                <?php echo $row['categoria_producto']; ?>
                            </div>

                            <p class="text-custom-silver dark:text-custom-gray mb-6 flex-grow">
                                <?php echo $row['descripcion_producto']; ?>
                            </p>

                            <div class="mt-auto">
                                <div class="flex items-baseline gap-2 mb-4">
                                    <span class="text-3xl font-bold text-custom-black dark:text-custom-silverLight">
                                        $<?php echo number_format($row['precio_producto'], 2); ?>
                                    </span>
                                </div>

                                <!-- In the carousel section, change the button onclick to: -->
                                <button
                                    onclick="addToCart(<?php echo $row['id_producto']; ?>)"
                                    class="w-full md:w-auto bg-custom-orange hover:bg-custom-wineDark text-custom-silverLight px-6 py-2 rounded-lg flex items-center justify-center gap-2"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No hay productos disponibles</p>";
                }
                ?>
            </div>

            <!-- Botones de Navegación (Mismo Estilo Anterior) -->
            <button id="prev-button" class="absolute left-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <button id="next-button" class="absolute right-4 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Indicadores -->
        <div id="carousel-indicators" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2"></div>
        </div>
    </div>
    </main>

    <!-- Productos Destacados -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
        <?php while ($producto = $result_productos->fetch_assoc()): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <?php
                $id_producto = $producto['id_producto'];
                $rutaImagen = obtenerRutasArchivos($id_producto);
                ?>
                <img src="<?php echo $rutaImagen; ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2 dark:text-white"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-2">Código: <?php echo htmlspecialchars($producto['numero_de_parte']); ?></p>
                    <p class="text-custom-blue dark:text-blue-400 font-bold mb-4">$<?php echo number_format($producto['precio_producto'], 2); ?></p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button
                                onclick="addToCart(<?php echo $producto['id_producto']; ?>)"
                                class="bg-custom-blue hover:bg-custom-blue-light text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Agregar al Carrito
                            </button>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Stock: <?php echo $producto['stock_producto']; ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
        const track = document.getElementById('carousel-track');
        const slides = Array.from(track.children);
        const prevButton = document.getElementById('prev-button');
        const nextButton = document.getElementById('next-button');
        const totalSlides = slides.length;
        let currentIndex = 0;

        function updateCarousel() {
            const slideWidth = slides[0].offsetWidth; // Obtener el ancho de un slide
            track.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % totalSlides; // Reiniciar al llegar al último
            updateCarousel();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + totalSlides) % totalSlides; // Ir al último si es negativo
            updateCarousel();
        }

        nextButton.addEventListener('click', nextSlide);
        prevButton.addEventListener('click', prevSlide);

        // Auto-rotación cada 5s
        setInterval(nextSlide, 8000);

        window.addEventListener('resize', updateCarousel);
    });
    
    </script>
</body>
</html>