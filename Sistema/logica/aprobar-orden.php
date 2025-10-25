<?php
require 'conexionbdd.php';
session_start();

if (!isset($_SESSION['id']) || !isset($_GET['id'])) {
    header('Location: ../panelAdmin/ordenes.php');
    exit();
}

$orden_id = $_GET['id'];
$sql = "UPDATE ordenes SET estado = 'aceptada' WHERE id_orden = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    header('Location: ../panelAdmin/ordenes.php?error=Error al preparar la consulta');
    exit();
}
$stmt->bind_param("i", $orden_id);

if ($stmt->execute()) {
    // Obtener id_producto y cantidad de la tabla detalle_orden
    $sql_detalle = "SELECT id_producto, cantidad FROM detalle_orden WHERE id_orden = ?";
    $stmt_detalle = $conn->prepare($sql_detalle);
    if ($stmt_detalle === false) {
        header('Location: ../panelAdmin/ordenes.php?error=Error al preparar la consulta de detalle');
        exit();
    }
    $stmt_detalle->bind_param("i", $orden_id);
    $stmt_detalle->execute();
    $result_detalle = $stmt_detalle->get_result();

    while ($row = $result_detalle->fetch_assoc()) {
        $id_producto = $row['id_producto'];
        $cantidad = $row['cantidad'];

        // Descontar la cantidad de la tabla productos
        $sql_update_producto = "UPDATE productos SET stock_producto = stock_producto - ? WHERE id_producto = ?";
        $stmt_update_producto = $conn->prepare($sql_update_producto);
        if ($stmt_update_producto === false) {
            header('Location: ../panelAdmin/ordenes.php?error=Error al preparar la consulta de actualización de producto');
            exit();
        }
        $stmt_update_producto->bind_param("ii", $cantidad, $id_producto);
        $stmt_update_producto->execute();
    }

    $stmt_detalle->close();
    $stmt_update_producto->close();

    header('Location: ../panelAdmin/ordenes.php?mensaje=Orden aprobada exitosamente');
} else {
    header('Location: ../panelAdmin/ordenes.php?error=Error al aprobar la orden');
}
$stmt->close();
$conn->close();
exit();
?>