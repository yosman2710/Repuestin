<?php
require '../logica/conexionbdd.php';
session_start();

// Get pending orders
$query = "SELECT o.*, c.nombre_empresa, c.rif
          FROM ordenes o
          INNER JOIN clientes c ON o.cliente_id = c.id
          WHERE o.estado = 'pendiente'
          ORDER BY o.fecha_creacion DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <!-- ... head content ... -->
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <main class="p-6">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Órdenes Pendientes</h2>

        <div class="grid gap-6">
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold dark:text-white">
                                Orden #<?php echo $order['id_orden']; ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Cliente: <?php echo htmlspecialchars($order['nombre_empresa']); ?> (RIF: <?php echo htmlspecialchars($order['rif']); ?>)
                            </p>
                            <p class="text-gray-500 dark:text-gray-400">
                                Fecha: <?php echo date('d/m/Y H:i', strtotime($order['fecha_creacion'])); ?>
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="processOrder(<?php echo $order['id_orden']; ?>, 'aceptada')"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                Aceptar
                            </button>
                            <button onclick="processOrder(<?php echo $order['id_orden']; ?>, 'rechazada')"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                Rechazar
                            </button>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <?php
                    $detail_query = "SELECT d.*, p.nombre_producto, p.numero_de_parte 
                                   FROM detalle_orden d 
                                   INNER JOIN productos p ON d.id_producto = p.id_producto 
                                   WHERE d.id_orden = ?";
                    $stmt = $conn->prepare($detail_query);
                    $stmt->bind_param("i", $order['id_orden']);
                    $stmt->execute();
                    $details = $stmt->get_result();
                    ?>
                    <div class="mt-4">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left dark:text-gray-400">
                                    <th class="pb-2">Producto</th>
                                    <th class="pb-2">Cantidad</th>
                                    <th class="pb-2">Precio</th>
                                    <th class="pb-2">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="dark:text-gray-300">
                                <?php while ($detail = $details->fetch_assoc()): ?>
                                    <tr>
                                        <td class="py-2">
                                            <?php echo htmlspecialchars($detail['nombre_producto']); ?>
                                            <br>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Código: <?php echo htmlspecialchars($detail['numero_de_parte']); ?>
                                            </span>
                                        </td>
                                        <td class="py-2"><?php echo $detail['cantidad']; ?></td>
                                        <td class="py-2">$<?php echo number_format($detail['precio_unitario'], 2); ?></td>
                                        <td class="py-2">$<?php echo number_format($detail['cantidad'] * $detail['precio_unitario'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script>
    function processOrder(orderId, action) {
        if (!confirm(`¿Está seguro que desea ${action === 'aceptada' ? 'aceptar' : 'rechazar'} esta orden?`)) {
            return;
        }

        fetch('../logica/process-order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=${orderId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Orden procesada exitosamente');
                location.reload();
            } else {
                alert(data.message || 'Error al procesar la orden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la orden');
        });
    }
    </script>
</body>
</html>