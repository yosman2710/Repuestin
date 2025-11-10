<?php
require 'conexionbdd.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

try {
    $conn->begin_transaction();

    // Create new order
    $client_id = $_SESSION['id'];
    $stmt = $conn->prepare("INSERT INTO ordenes (cliente_id) VALUES (?)");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Get cart items
    $stmt = $conn->prepare("SELECT c.*, p.precio_producto FROM carrito c 
                           INNER JOIN productos p ON c.producto_id = p.id_producto 
                           WHERE c.cliente_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Insert order details
    $stmt = $conn->prepare("INSERT INTO detalle_orden (id_orden, id_producto, cantidad, precio_unitario) 
                           VALUES (?, ?, ?, ?)");

    while ($item = $result->fetch_assoc()) {
        $stmt->bind_param("iiid", 
            $order_id, 
            $item['producto_id'], 
            $item['cantidad'], 
            $item['precio_producto']
        );
        $stmt->execute();
    }

    // Clear cart
    $stmt = $conn->prepare("DELETE FROM carrito WHERE cliente_id = ?");
    $stmt->bind_param("i", $client_id);
    $stmt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al procesar la orden: ' . $e->getMessage()]);
}