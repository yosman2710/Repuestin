<?php
require '../logica/validar.php';
require '../logica/conexionbdd.php';

session_start();

if (!isset($_SESSION['id'])) {
  header('location:../login-sesion/loginCliente.php?error_message=Por favor inicie sesión');
  exit();
}

$client_id = $_SESSION['id'];
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre_encargado = trim($_POST['nombre_encargado']);
  $correo = trim($_POST['correo']);
  $contrasena = trim($_POST['contrasena']);
  $cedula_encargado = trim($_POST['cedula_encargado']);

  if (!empty($nombre_encargado) && !empty($correo)) {
    $query = "UPDATE clientes SET nombre_encargado = ?, correo = ?, cedula_encargado = ?";
    $params = [$nombre_encargado, $correo, $cedula_encargado];
    $types = "sss";

    // Only update password if a new one is provided
    if (!empty($contrasena)) {
      $query .= ", contrasena = ?";
      $params[] = $contrasena;
      $types .= "s";
    }

    $query .= " WHERE id = ?";
    $params[] = $client_id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
      $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Datos actualizados correctamente</div>';
    } else {
      $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Error al actualizar los datos</div>';
    }
  }
}

// Get client data
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
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
  <title>Mis Datos - Autorepuestos TirameAlgo</title>
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
        <a href="./cliente.php" class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

  <!-- Main Content -->
  <main class="pt-24 px-6 pb-20 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 dark:text-white">Mis Datos</h2>

    <?php echo $message; ?>

    <form method="POST" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
      <!-- Company Information (Read-only) -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Datos de la Empresa</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de la Empresa</label>
            <div class="relative">
              <input type="text" value="<?php echo htmlspecialchars($client_data['nombre_empresa']); ?>"
                class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:text-white rounded-md pr-10" readonly>
              <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">🔒</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">RIF</label>
            <div class="relative">
              <input type="text" value="<?php echo htmlspecialchars($client_data['rif']); ?>"
                class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:text-white rounded-md pr-10" readonly>
              <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">🔒</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono Empresa</label>
            <div class="relative">
              <input type="text" value="<?php echo htmlspecialchars($client_data['telefono_empresa']); ?>"
                class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:text-white rounded-md pr-10" readonly>
              <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">🔒</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
            <div class="relative">
              <input type="text" value="<?php echo htmlspecialchars($client_data['direccion']); ?>"
                class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 dark:text-white rounded-md pr-10" readonly>
              <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">🔒</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Editable Information -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-4 dark:text-white">Datos del Encargado</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre del Encargado</label>
            <input type="text" name="nombre_encargado" value="<?php echo htmlspecialchars($client_data['nombre_encargado']); ?>"
              class="w-full px-3 py-2 border border-transparent dark:border-transparent rounded-md dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cédula</label>
            <input type="text" name="cedula_encargado" value="<?php echo htmlspecialchars($client_data['cedula_encargado']); ?>"
              class="w-full px-3 py-2 border border-transparent dark:border-transparent rounded-md dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Correo Electrónico</label>
            <input type="email" name="correo" value="<?php echo htmlspecialchars($client_data['correo']); ?>"
              class="w-full px-3 py-2 border border-transparent dark:border-transparent rounded-md dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nueva Contraseña (opcional)</label>
            <input type="password" name="contrasena" placeholder="Dejar en blanco para mantener la actual"
              class="w-full px-3 py-2 border border-transparent dark:border-transparent rounded-md dark:bg-gray-700 dark:text-white focus:border-blue-500 dark:focus:border-blue-400 transition-colors duration-200">
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit"
          class="bg-custom-blue hover:bg-custom-blue-light text-white px-6 py-2 rounded-md transition-colors duration-200">
          Guardar Cambios
        </button>
      </div>
    </form>
  </main>

  <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
    <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
  </footer>
</body>

</html>