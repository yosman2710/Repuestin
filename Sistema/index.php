<!DOCTYPE html>
<html lang="es" class="dark">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorepuestos Johbri, C.A.</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./css/index_global_style.css">
    <script src="./js/tailwind_config.js"></script>
    <script src="./js/mantener_modo_claro_oscuro.js"></script>

</head>
<body class="bg-pattern transition-colors duration-200">
    <nav class="bg-custom-blue/95 backdrop-blur-sm dark:bg-gray-800/95 text-white px-6 py-4 flex justify-between items-center fixed w-full top-0 z-50 shadow-lg">
        <div class="text-xl sm:text-2xl font-bold">Autorepuestos Johbri, C.A.</div>
        <div class="flex items-center gap-4">
            <button
                onclick="toggleDarkMode()"
                class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200"
                aria-label="Alternar entre modo oscuro y claro"
            >
                <span class="dark:hidden">🌙</span>
                <span class="hidden dark:inline">☀️</span>
            </button>
        </div>
    </nav>

    <main class="min-h-screen flex flex-col items-center justify-center px-4 text-center relative">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-custom-blue dark:text-white mb-6 drop-shadow-lg">
                ¡Tu auto, nuestra pasión!
            </h1>
            <p class="text-xl sm:text-2xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed mb-12">
                Descubre una amplia gama de repuestos automotrices de alta calidad, perfectos para mantener tu vehículo en óptimas condiciones. Navega por nuestras categorías y aprovecha ofertas exclusivas que harán que tu auto brille en cada viaje.
            </p>
        </div>
        <a
            href="login-sesion/loginCliente.php"
            class="px-8 py-3 text-lg bg-custom-blue-light text-white dark:bg-custom-blue dark:text-white rounded-full hover:bg-custom-blue dark:hover:bg-gray-600 transition-colors duration-200 font-semibold shadow-md"
        >
            Iniciar Sesión
        </a>
    </main>

    <footer class="bg-custom-blue/95 dark:bg-gray-800/95 backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
        <p>&copy; 2024 Autorepuestos Johbri, C.A. - Todos los derechos reservados</p>
    </footer>

</body>
</html>