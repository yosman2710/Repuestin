<?php
require '../logica/conexionbdd.php';
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

$_SESSION['ultimo_acceso'] = time();

$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';

// Obtener el ID del cliente desde la URL
$cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar si el ID del cliente es válido
if ($cliente_id > 0) {
    // Preparar la consulta para obtener la información del cliente
    $query = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $query->bind_param("i", $cliente_id);
    $query->execute();
    $result = $query->get_result();

    // Verificar si se encontró el cliente
    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
    } else {
        header('location:../login-sesion/login.php?error_message=Cliente no encontrado');
        exit();
    }
} else {
    header('location:../login-sesion/login.php?error_message=ID de cliente inválido');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <title>Agregar cliente - Autorepuestos TirameAlgo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

</head>
<body class="bg-pattern transition-colors duration-200">

    <!-- Alerta de errores -->
<div id="alertaError" class="fixed top-0 left-0 right-0 z-50 transform -translate-y-full transition-transform duration-300 ease-in-out">
    <div class="max-w-4xl mx-auto mt-20 p-4 rounded-md bg-red-50 dark:bg-red-900 border border-red-500">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <!-- Ícono de error -->
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3" id="mensajeError">
                <?php echo isset($error_message) ? htmlspecialchars($error_message) : ''; ?>
            </div>
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="cerrarAlerta()" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 dark:hover:bg-red-800 transition-colors duration-200">
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
    </div>
</div>

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
                <a href="../logica/cerrar-sesion.php">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="pt-24 px-6 pb-20">
        <div class="max-w-4xl mx-auto">
            <!-- Encabezado -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-custom-black dark:text-custom-silverLight">Editar Cliente</h1>
                <p class="text-gray-600 dark:text-gray-400">Modifique los campos necesarios para actualizar la información del cliente empresarial</p>
            </div>

            <form action="../logica/actualizar-clientes.php" method="POST" class="bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6" novalidate>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">
                <div class="space-y-6">
                    <h2 class="text-lg font-semibold text-custom-black dark:text-custom-silverLight border-b border-gray-200 dark:border-gray-700 pb-2">
                        Información de la Empresa
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre de la Empresa *
                            </label>
                            <input type="text" required
                                id="nombre_empresa"
                                name="nombre_empresa"
                                value="<?php echo htmlspecialchars($cliente['nombre_empresa']); ?>"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>

                        <!-- RIF -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                RIF *
                            </label>
                            <input type="text" required
                                id="rif"
                                name="rif"
                                value="<?php echo htmlspecialchars($cliente['rif']); ?>"
                                placeholder="J-12345678-9"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono de la Empresa *
                            </label>
                            <input type="tel" required
                                id="telefono_empresa"
                                name="telefono_empresa"
                                value="<?php echo htmlspecialchars($cliente['telefono_empresa']); ?>"
                                placeholder="0212-1234567"
                                class= "w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Dirección de la Sede *
                        </label>
                        <textarea required name="direccion" id="direccion"
                                rows="2"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                    </div>

                    <!-- Información del Contacto -->
                    <h2 class="text-lg font-semibold text-custom-black dark:text-custom-silverLight border-b border-gray-200 dark:border-gray-700 pb-2 mt-8">
                        Información del Contacto Principal
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre del Contacto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nombre Completo *
                            </label>
                            <input type="text" required name="nombre_contacto" id="nombre_contacto"
                                value="<?php echo htmlspecialchars($cliente['nombre_encargado']); ?>"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>

                        <!-- Cédula del Contacto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cédula *
                            </label>
                            <input type="text" required name="cedula_encargado" id="cedula_encargado"
                                value="<?php echo htmlspecialchars($cliente['cedula_encargado']); ?>"
                                placeholder="V-12345678"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>

                        <!-- Teléfono del Contacto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Teléfono Encargado *
                            </label>
                            <input type="tel" required name="telefono_encargado" id="telefono_encargado"
                                value="<?php echo htmlspecialchars($cliente['telefono_encargado']); ?>"
                                placeholder="0414-1234567"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>
                    </div>

                    <!-- Información de Acceso -->
                    <h2 class="text-lg font-semibold text-custom-black dark:text-custom-silverLight border-b border-gray-200 dark:border-gray-700 pb-2 mt-8">
                        Información de Acceso al Sistema
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Correo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Correo Electrónico Empresa*
                            </label>
                            <input type="email" required name="correo_empresa" id="correo_empresa"
                                value="<?php echo htmlspecialchars($cliente['correo']); ?>"
                                class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" required
                                    id="password"
                                    name="password"
                                    value="<?php echo htmlspecialchars($cliente['contrasena']); ?>"
                                    class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                                <button type="button"
                                        onclick="togglePassword()"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg id="showPassword" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="hidePassword" class="h-5 w-5 text-gray-500 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <?php if ($cliente['estado_cliente'] == 'Inactivo'): ?>
                            <div class="flex items-center mb-4">
                                <input id="Usuario_bloqueado" name="Usuario_bloqueado" type="checkbox" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" checked>
                                <label for="Usuario_bloqueado" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Usuario bloqueado</label>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center mb-4">
                                <input id="Usuario_bloqueado" name="Usuario_bloqueado" type="checkbox" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="Usuario_bloqueado" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Usuario Bloqueado</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-8 flex justify-end space-x-4">
                    <button onclick="history.back()" type="button"
                            class="px-4 py-2 bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange
                                dark:hover:bg-custom-red text-custom-silver rounded-md transition-colors duration-200">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange
                                dark:hover:bg-custom-red text-custom-silver rounded-md transition-colors duration-200">
                        Actualizar Cliente
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>
    <script>
        
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const showIcon = document.getElementById('showPassword');
        const hideIcon = document.getElementById('hidePassword');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            showIcon.classList.add('hidden');
            hideIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            showIcon.classList.remove('hidden');
            hideIcon.classList.add('hidden');
        }
    }

    function mostrarAlerta(mensajes) {
        const alertaError = document.getElementById('alertaError');
        const mensajeError = document.getElementById('mensajeError');
        // Crear lista de errores con estilo mejorado
        const listaErrores = mensajes.map(error =>
            `<p class="text-sm text-red-700 dark:text-red-200">• ${error}</p>`
        ).join('');
        mensajeError.innerHTML = listaErrores;
        // Mostrar alerta con animación
        alertaError.classList.remove('-translate-y-full');
        // Desplazar la página hacia arriba
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cerrarAlerta() {
        const alertaError = document.getElementById('alertaError');
        alertaError.classList.add('-translate-y-full');
    }

    function cerrarAlertaExito() {
        const alertaExito = document.getElementById('alertaExito');
        alertaExito.classList.add('-translate-y-full');
    }

    <?php if ($error_message): ?>
    document.addEventListener('DOMContentLoaded', function() {
        const alertaError = document.getElementById('alertaError');
        alertaError.classList.remove('-translate-y-full');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    <?php endif; ?>

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