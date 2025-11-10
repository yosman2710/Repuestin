<?php
session_start();
if(!ISSET($_SESSION['id'])){
  header('location:../login-sesion/login.php');
}
else{
  if((time() - $_SESSION['time']) > 600){
    session_unset();
    session_destroy();
    header('location:../login-sesion/login.php');
  }
}
$_SESSION['time'] = time();
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/ico" href="../assets/images/configuraciones.ico">
  <title>Reportes - Autorepuestos TirameAlgo</title>
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
    <div class="max-w-4xl mx-auto">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Reportes</h1>
        <p class="text-gray-600 dark:text-gray-400">Seleccione el tipo de reporte y utilice los filtros para generar reportes específicos</p>
      </div>
      <form class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Tipo de Reporte
          </label>
          <select name="tipo_reporte" id="tipo_reporte" class="w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue" onchange="toggleFilters()">
            <option value="">Seleccione un tipo de reporte</option>
            <option value="clientes">Clientes</option>
            <option value="repuestos">Repuestos</option>
          </select>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Filtro Clientes Activos/Inactivos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado de Clientes
            </label>
            <select name="estado_clientes" id="estado_clientes" class="cliente-filter w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue" disabled>
              <option value="">Seleccione un estado</option>
              <option value="activos">Activos</option>
              <option value="inactivos">Inactivos</option>
            </select>
          </div>
          <!-- Filtro Repuestos con Stock/Sin Stock -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Estado de Stock
            </label>
            <select name="estado_stock" id="estado_stock" class="repuesto-filter w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue" disabled>
              <option value="">Seleccione un estado</option>
              <option value="con_stock">Con Stock</option>
              <option value="sin_stock">Sin Stock</option>
            </select>
          </div>
          <!-- Filtro Categoría de Repuestos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Categoría de Repuestos
            </label>
            <select name="categoria_repuestos" id="categoria_repuestos" class="repuesto-filter w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue" disabled>
              <option value="">Seleccione una categoría</option>
              <option>Frenos</option>
              <option>Inyección</option>
              <option>Estoperas</option>
              <option>Suspensión</option>
              <option>Motor</option>
              <option>Filtros</option>
              <option>Carrocería</option>
              <option>Accesorios</option>
              <option>Transmisión</option>
              <option>Electricidad</option>
              <option>Otros</option>
            </select>
          </div>
          <!-- Filtro Marca de Repuestos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Marca de Repuestos
            </label>
            <select name="marca_repuestos" id="marca_repuestos" class="repuesto-filter w-full px-4 py-2 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-custom-blue" disabled>
              <option value="">Seleccione una marca</option>
              <option>Marca A</option>
              <option>Marca B</option>
              <option>Marca C</option>
            </select>
          </div>
        </div>
        <div class="mt-6">
          <button type="submit" class="w-full px-4 py-2 bg-custom-blue text-white rounded-md hover:bg-custom-blue-light transition-colors duration-200">Generar Reporte</button>
        </div>
      </form>
      <!-- Tabla de Resultados -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resultados del Reporte</h2>
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
              <th class="px-4 py-2">Código</th>
              <th class="px-4 py-2">Nombre</th>
              <th class="px-4 py-2">Categoría</th>
              <th class="px-4 py-2">Marca</th>
              <th class="px-4 py-2">Stock</th>
              <th class="px-4 py-2">Estado Cliente</th>
            </tr>
          </thead>
          <tbody id="tablaResultados">
            <!--
          generar filas de tabla resultado con php y colocar resultados de filtros
            -->
          </tbody>
        </table>
      </div>
    </div>
    </main>
    <script>
    function toggleFilters() {
    const reportType = document.getElementById('tipo_reporte').value;
    const clienteFilters = document.querySelectorAll('.cliente-filter');
    const repuestoFilters = document.querySelectorAll('.repuesto-filter');

    if (reportType === 'clientes') {
        clienteFilters.forEach(filter => filter.disabled = false);
        repuestoFilters.forEach(filter => filter.disabled = true);
    } else if (reportType === 'repuestos') {
        clienteFilters.forEach(filter => filter.disabled = true);
        repuestoFilters.forEach(filter => filter.disabled = false);
    } else {
        clienteFilters.forEach(filter => filter.disabled = true);
        repuestoFilters.forEach(filter => filter.disabled = true);
    }

    updateTableHeaders(reportType);
}

    function updateTableHeaders(reportType) {
        const tableHead = document.querySelector('thead');
        tableHead.innerHTML = '';

        if (reportType === 'clientes') {
            tableHead.innerHTML = `
                <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <th class="px-4 py-2">ID Cliente</th>
                    <th class="px-4 py-2">Empresa</th>
                    <th class="px-4 py-2">Correo</th>
                    <th class="px-4 py-2">Teléfono</th>
                    <th class="px-4 py-2">Estado</th>
                </tr>
            `;
        } else if (reportType === 'repuestos') {
            tableHead.innerHTML = `
                <tr class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                    <th class="px-4 py-2">Código</th>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Categoría</th>
                    <th class="px-4 py-2">Marca</th>
                    <th class="px-4 py-2">Stock</th>
                </tr>
            `;
        }
    }
  </script>
  </body>
</html>
