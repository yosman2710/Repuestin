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


$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$estado = isset($_GET['estado']) ? $conn->real_escape_string($_GET['estado']) : '';

$sql = "SELECT * FROM clientes";
$conditions = [];

if ($search) {
    $conditions[] = "nombre_empresa LIKE '%$search%'";
}

if ($estado) {
    $conditions[] = "estado_cliente = '$estado'";
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$result = $conn->query($sql);

$clientes = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
  <title>Gestión de Clientes - Autorepuestos TirameAlgo</title>
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
              <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
          </div>
      </div>
  </nav>

  <!-- Contenido Principal -->
  <main class="pt-24 px-6 pb-20">
      <!-- Encabezado y Botón Agregar -->
      <div class="max-w-7xl mx-auto mb-6 flex justify-between items-center">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Clientes</h1>
          <a href="agregarCliente.php"
              class="bg-custom-blue hover:bg-custom-blue-light text-white px-4 py-2 rounded-md
                  transition-colors duration-200 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Agregar Cliente
          </a>
      </div>

      <!-- Filtros -->
      <div class="max-w-7xl mx-auto bg-custom-silverLight dark:bg-custom-steelDark rounded-lg shadow-md p-6 mb-6">
          <form method="GET" action="verClientes.php" class="flex flex-col md:flex-row gap-4 items-end">
          <div class="w-full md:w-1/3">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Buscar cliente
              </label>
              <input
              type="text"
              name="search"
              placeholder="Nombre de la empresa"
              class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                    dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
          </div>
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Estado del cliente
            </label>
            <select name="estado" class="w-full px-4 py-2 rounded-md border border-custom-gray dark:border-custom-gray
                        dark:bg-custom-gray dark:text-custom-silverLight focus:ring-2 focus:ring-custom-red">
                <option value="">Todos</option>
                <option value="activo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Activo' ? 'selected' : ''); ?>>Activo</option>
                <option value="inactivo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo' ? 'selected' : ''); ?>>Inactivo</option>
            </select>
        </div>
          <button type="submit" class="w-full md:w-auto px-4 py-2 bg-custom-blue hover:bg-custom-blue-light text-white
              rounded-md transition-colors duration-200">
              Buscar
          </button>
          <?php if (!empty($search) || !empty($estado)): ?>
            <a href="verClientes.php" class="w-full md:w-auto px-4 py-2 bg-custom-silverLight hover:bg-gray-600 dark:bg-custom-steelDark
                        dark:hover:bg-gray-700 text-white rounded-md transition-colors duration-200 text-center">
                Cancelar
            </a>
        <?php endif; ?>
          </form>
      </div>

      <!-- Lista de Clientes Desplegable -->
      <div class="max-w-7xl mx-auto">
          <?php foreach ($clientes as $cliente): ?>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden mb-4">
              <button onclick="toggleCliente('cliente<?php echo $cliente['id']; ?>')"
                      class="w-full px-6 py-4 flex justify-between items-center text-left text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700">
                  <div class="flex items-center">
                      <span class="text-lg font-semibold"><?php echo $cliente['nombre_empresa']; ?></span>
                    
                      <?php if ($cliente['estado_cliente'] == 'Inactivo'): ?>
                      <span class="ml-3 px-2 py-1 text-sm bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full">
                          Usuario Bloqueado
                      </span>
                      <?php else: ?>
                        <span class="ml-3 px-2 py-1 text-sm bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                            Activo
                        </span>
                        <?php endif; ?>

                  </div>
                  <svg id="arrow-cliente<?php echo $cliente['id']; ?>" class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
              </button>

              <div id="info-cliente<?php echo $cliente['id']; ?>" class="hidden p-6 border-t border-gray-200 dark:border-gray-700">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Información de la Empresa -->
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">RIF</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['rif']; ?></p>
                      </div>
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">Teléfono Empresa</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['telefono_empresa']; ?></p>
                      </div>
                      <div class="md:col-span-2">
                          <p class="text-sm text-gray-600 dark:text-gray-400">Dirección de la Sede</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['direccion']; ?></p>
                      </div>

                      <!-- Información del Contacto Principal -->
                      <div class="md:col-span-2 mt-4">
                          <h3 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
                              Información del Contacto Principal
                          </h3>
                      </div>
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">Nombre Completo</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['nombre_encargado']; ?></p>
                      </div>
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">Cédula</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['cedula_encargado']; ?></p>
                      </div>
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">Teléfono Móvil</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['telefono_encargado']; ?></p>
                      </div>
                      <div>
                          <p class="text-sm text-gray-600 dark:text-gray-400">Correo Electronico</p>
                          <p class="text-lg text-gray-900 dark:text-white"><?php echo $cliente['correo']; ?></p>
                      </div>

                      <!-- Botones de acción -->
                      <div class="flex items-end justify-end md:col-span-2">
                          <div class="flex space-x-2">
                              <a href="editar-cliente.php?id=<?php echo $cliente['id']; ?>"
                                 class="text-yellow-500 hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300"
                                 title="Editar">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                  </svg>
                              </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
  </main>

  <footer class="bg-custom-steelDark dark:bg-custom-black text-white text-center py-4 fixed bottom-0 w-full text-sm">
      <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
  </footer>

  <script>
    
      function toggleCliente(clienteId) {
          const info = document.getElementById('info-' + clienteId);
          const arrow = document.getElementById('arrow-' + clienteId);
          if (info.classList.contains('hidden')) {
              info.classList.remove('hidden');
              arrow.classList.add('rotate-180');
          } else {
              info.classList.add('hidden');
              arrow.classList.remove('rotate-180');
          }
      }
  </script>
</body>
</html>