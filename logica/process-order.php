<?php
require 'conexionbdd.php';
session_start();

if (!isset($_POST['order_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$order_id = $_POST['order_id'];
$action = $_POST['action'];

try {
    $conn->begin_transaction();

    // Update order status
    $stmt = $conn->prepare("UPDATE ordenes SET estado = ? WHERE id_orden = ?");
    $stmt->bind_param("si", $action, $order_id);
    $stmt->execute();

    // If order is accepted, update product stock
    if ($action === 'aceptada') {
        // Get order details
        $stmt = $conn->prepare("SELECT id_producto, cantidad FROM detalle_orden WHERE id_orden = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Update stock for each product
        $update_stmt = $conn->prepare("UPDATE productos SET stock_producto = stock_producto - ? WHERE id_producto = ?");

        while ($detail = $result->fetch_assoc()) {
            $update_stmt->bind_param("ii", $detail['cantidad'], $detail['id_producto']);
            $update_stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al procesar la orden: ' . $e->getMessage()]);
}