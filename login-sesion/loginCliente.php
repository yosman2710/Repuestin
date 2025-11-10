<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autorepuestos TirameAlgo, C.A.</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <!-- Incluir Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

    <!-- Incluir Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body class="bg-pattern transition-colors duration-200">
    <!-- Contenedor para Particles.js (fondo) -->
    <div id="particles-js" class="fixed inset-0 z-0"></div>

    <nav class="bg-custom-wineDeep backdrop-blur-sm dark:bg-custom-wineDeep text-custom-silver px-6 py-4 flex justify-between items-center fixed w-full top-0 z-50 shadow-lg">
        <div>
            <a href="../index.php"
                class="text-xl hover:text-custom-gray transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="text-sm">Volver</span>
            </a>
        </div>
        <button
            onclick="toggleDarkMode()"
            class="p-2 rounded-full bg-custom-wineDark dark:bg-custom-red hover:bg-custom-red dark:hover:bg-custom-wineDark transition-colors duration-200"
            aria-label="Alternar entre modo oscuro y claro"
        >
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>
    </nav>

    <main class="min-h-screen flex flex-col items-center justify-center px-4 relative z-10">
        <div class="bg-custom-silverLight dark:bg-custom-steelDark p-8 rounded-lg shadow-xl max-w-md w-full">
            <!-- Alerta de Error -->
            <div id="errorAlert" class="mb-6 p-4 rounded-md bg-red-50 dark:bg-red-900/50 border border-red-500 hidden">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <!-- Ícono de error -->
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex justify-center items-center">
                        <p class="text-sm text-red-500 dark:text-red-400">
                        <?php
                            if (isset($_GET['error_message'])) {
                                echo urldecode($_GET['error_message']);
                            }
                        ?>
                        </p>
                    </div>
                    <!-- Botón cerrar -->
                    <div class="ml-auto pl-3">
                        <button type="button"
                            onclick="document.getElementById('errorAlert').classList.add('hidden')"
                            class="text-red-400 hover:text-red-500 focus:outline-none">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-custom-black dark:text-custom-silver text-center mb-6">
                Iniciar Sesión
            </h2>
            <form class="space-y-6" action="../logica/loguear-cliente.php" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-custom-black dark:text-custom-silver mb-1">
                            Correo Electrónico
                        </label>
                        <input
                            type="email"
                            id="username"
                            name="username"
                            required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-red dark:focus:border-red-500 dark:bg-custom-gray dark:text-white"
                        >
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-custom-black dark:text-custom-silver mb-1">
                            Contraseña
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-red dark:focus:border-red-500 dark:bg-custom-gray dark:text-white"
                        >
                    </div>
                    <!-- Enlaces de navegación -->
                    <div class="flex items-center justify-between">
                        <a href="./login.php"
                        class="text-sm text-custom-red dark:text-custom-silverTitan hover:underline">
                            ¿Eres Admin?
                        </a>
                        <a href="./olvidarContraseña.php"
                        class="text-sm text-custom-red dark:text-custom-silverTitan hover:underline">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>
                <button
                    type="submit"
                    class="w-full bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange
                        dark:hover:bg-custom-red text-custom-silver py-2 px-4 rounded-md
                        transition-colors duration-200 font-semibold mt-6"
                >
                    Ingresar al Sistema
                </button>
            </form>
            <div class="mt-4 text-center">
                <a href="../index.php" class="text-custom-red dark:text-custom-silverTitan hover:underline text-sm">
                    Volver al inicio
                </a>
            </div>
        </div>
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

    <!-- Incluir la configuración de Particles.js -->
    <script src="../js/particles-config.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = "<?php echo isset($_GET['error_message']) ? urldecode($_GET['error_message']) : ''; ?>";
            if (errorMessage) {
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        });
    </script>
</body>
</html>