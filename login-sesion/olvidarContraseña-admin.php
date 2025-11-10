<!DOCTYPE html>
<html lang="es" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Contraseña</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="../js/tailwind_config.js"></script>
  <script src="../js/mantener_modo_claro_oscuro.js"></script>
  <link rel="stylesheet" href="../css/global_style.css">

</head>
<body class="bg-pattern transition-colors duration-200">
  <nav class="bg-custom-blue/95 backdrop-blur-sm dark:bg-gray-800/95 text-white px-6 py-4 flex justify-between items-center fixed w-full top-0 z-50 shadow-lg">
    <div>
      <a href="./login.php" class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
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
<main class="min-h-screen flex flex-col items-center justify-center px-4">
  <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-xl max-w-md w-full">
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
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 111.414 1.414L11.414 10l4.293 4.293a1 1 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 01-1.414-1.414L8.586 10 4.293 5.707a1 1 010-1.414z"
                                  clip-rule="evenodd"/>
                          </svg>
                      </button>
                  </div>
              </div>
          </div>
    <h2 class="text-3xl font-semibold mb-6 text-center text-custom-blue dark:text-white">Recuperar Contraseña</h2>
    <form id="passwordRecoveryForm" action="../logica/enviar-contrasena-admin.php" method="GET">
      <div class="mb-5">
        <label for="email" class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Correo Electrónico:</label>
        <input type="email" id="email" name="email" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-blue dark:focus:border-blue-500 dark:bg-gray-700 dark:text-white" required>
      </div>
      <div class="flex justify-center">
        <button type="submit" class="bg-custom-blue hover:bg-custom-blue-light dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline">Recuperar Contraseña</button>
      </div>
    </form>
    <div id="successMessage" class="hidden text-center mt-8">
      <div class="flex flex-col items-center justify-center space-y-4">
        <!-- Email Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 text-custom-blue dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        <p class="text-gray-600 dark:text-gray-300">Revisa tu correo electrónico hemos enviado tus datos de acceso</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 break-all">
          <span class="email-preview"></span>
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Si no encuentras el correo, revisa la bandeja de correo no deseado</p>
      </div>
    </div>
  </div>
</main>
  <footer class="bg-custom-steelDark dark:bg-custom-black backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
    <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = "<?php echo isset($_GET['error_message']) ? urldecode($_GET['error_message']) : ''; ?>";
            if (errorMessage) {
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        });

    function showSuccessMessage(event) {
      event.preventDefault();
      const email = document.getElementById('email').value;
      document.getElementById('passwordRecoveryForm').classList.add('hidden');
      document.getElementById('successMessage').classList.remove('hidden');
      document.querySelector('.email-preview').textContent = email;
    }
  </script>
</body>
</html>