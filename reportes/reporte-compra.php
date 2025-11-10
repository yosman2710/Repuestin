<?php
require '../vendor/autoload.php'; // Cargar DOMPDF

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuración de DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true); // Permitir cargar recursos remotos como fuentes o imágenes
$dompdf = new Dompdf($options);

// Datos de la factura
$productos = [
    ['nombre' => 'Producto A', 'cantidad' => 2, 'precio' => 100],
    ['nombre' => 'Producto B', 'cantidad' => 1, 'precio' => 150],
    ['nombre' => 'Producto C', 'cantidad' => 3, 'precio' => 50],
];

$subtotal = 0;
foreach ($productos as $producto) {
    $subtotal += $producto['cantidad'] * $producto['precio'];
}
$iva = $subtotal * 0.16; // IVA al 16%
$total = $subtotal + $iva;

// Tailwind CSS styles
$tailwindCSS = '
<style>
@import url("https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css");

body {
    font-family: "Inter", sans-serif;
    margin: 0;
    padding: 0;
}

h1, h2, h3 {
    color: #1a202c;
}

table {
    border-spacing: 0;
    border-collapse: collapse;
}

th, td {
    padding: 0.5rem;
    border: 1px solid #e2e8f0;
}

th {
    background-color: #edf2f7;
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f7fafc;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.container {
    width: 100%;
    padding: 2rem;
    box-sizing: border-box;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.logo {
    max-width: 150px;
}
</style>
';

// HTML de la factura
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    ' . $tailwindCSS . '
</head>
<body class="bg-gray-100">
    <div class="container bg-white shadow-lg rounded-lg w-full h-full" style="min-height: 100vh;">
        <!-- Header -->
        <div class="header">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Factura</h1>
                <p class="text-sm text-gray-500">Fecha: ' . date('d/m/Y') . '</p>
            </div>
            <div class="flex justify-end">
                <img src="../images/Logo1.jpg" alt="Logo de la Empresa" class="logo" style="max-width: 150px;">
            </div>
        </div>

        <!-- Cliente -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-700">Datos del Cliente</h3>
            <p class="text-sm text-gray-500">Nombre: Juan Pérez</p>
            <p class="text-sm text-gray-500">Dirección: Calle Falsa 123</p>
        </div>

        <!-- Tabla de productos -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700">
                        <th class="py-2 px-4 border">Producto</th>
                        <th class="py-2 px-4 border">Cantidad</th>
                        <th class="py-2 px-4 border">Precio Unitario</th>
                        <th class="py-2 px-4 border">Subtotal</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($productos as $producto) {
                    $subtotal_producto = $producto['cantidad'] * $producto['precio'];
                    $html .= '
                    <tr>
                        <td class="py-2 px-4 border">' . $producto['nombre'] . '</td>
                        <td class="py-2 px-4 border text-center">' . $producto['cantidad'] . '</td>
                        <td class="py-2 px-4 border text-right">$' . number_format($producto['precio'], 2) . '</td>
                        <td class="py-2 px-4 border text-right">$' . number_format($subtotal_producto, 2) . '</td>
                    </tr>';
                }
$html .= '
                </tbody>
            </table>
        </div>

        <!-- Totales -->
        <div class="mt-6 text-right">
            <div class="flex justify-end items-center">
                <p class="text-gray-700 font-semibold">Subtotal:</p>
                <p class="ml-4 text-gray-800 font-bold">$' . number_format($subtotal, 2) . '</p>
            </div>
            <div class="flex justify-end items-center mt-2">
                <p class="text-gray-700 font-semibold">IVA (16%):</p>
                <p class="ml-4 text-gray-800 font-bold">$' . number_format($iva, 2) . '</p>
            </div>
            <div class="flex justify-end items-center mt-2">
                <p class="text-gray-700 font-semibold">Total:</p>
                <p class="ml-4 text-gray-800 font-bold">$' . number_format($total, 2) . '</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Gracias por tu compra</p>
            <p>Tu Empresa © ' . date('Y') . '</p>
        </div>
    </div>
</body>
</html>';

// Generar el PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // Configurar tamaño y orientación del papel
$dompdf->render();

// Descargar el PDF
$dompdf->stream("factura.pdf", ["Attachment" => true]);