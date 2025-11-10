<?php
session_start();
require 'conexionbdd.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Por favor inicie sesión']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $product_id = isset($_POST['product_id']) ? abs((int)$_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? abs((int)$_POST['quantity']) : 1;
    $client_id = $_SESSION['id'];

    // Validar que product_id sea mayor que 0
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de producto no válido']);
        exit();
    }

    // Validar que la cantidad sea al menos 1
    if ($quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'La cantidad debe ser al menos 1']);
        exit();
    }

    switch ($action) {
        case 'add':
            // Verificar stock disponible
            $stmt = $conn->prepare("SELECT stock_producto FROM productos WHERE id_producto = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit();
            }

            if ($quantity > $product['stock_producto']) {
                echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible']);
                exit();
            }

            // Verificar si el producto ya está en el carrito del cliente
            $stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE cliente_id = ? AND producto_id = ?");
            $stmt->bind_param("ii", $client_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Actualizar cantidad si el producto ya existe
                $row = $result->fetch_assoc();
                $new_quantity = $row['cantidad'] + $quantity;

                // Verificar que la nueva cantidad no exceda el stock
                if ($new_quantity > $product['stock_producto']) {
                    echo json_encode(['success' => false, 'message' => 'La cantidad total excedería el stock disponible']);
                    exit();
                }

                $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE cliente_id = ? AND producto_id = ?");
                $stmt->bind_param("iii", $new_quantity, $client_id, $product_id);
            } else {
                // Insertar nuevo producto al carrito
                $stmt = $conn->prepare("INSERT INTO carrito (cliente_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $client_id, $product_id, $quantity);
            }

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto agregado al carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito']);
            }
            break;

        case 'update':
            // Verificar stock disponible
            $stmt = $conn->prepare("SELECT stock_producto FROM productos WHERE id_producto = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit();
            }

            if ($quantity > $product['stock_producto']) {
                echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible']);
                exit();
            }

            if ($quantity === 0) {
                // Si la cantidad es 0, eliminar el producto del carrito
                $stmt = $conn->prepare("DELETE FROM carrito WHERE cliente_id = ? AND producto_id = ?");
                $stmt->bind_param("ii", $client_id, $product_id);
            } else {
                $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE cliente_id = ? AND producto_id = ?");
                $stmt->bind_param("iii", $quantity, $client_id, $product_id);
            }

            if ($stmt->execute()) {
                if ($quantity === 0) {
                    echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el carrito']);
            }
            break;

        case 'remove':
            $stmt = $conn->prepare("DELETE FROM carrito WHERE cliente_id = ? AND producto_id = ?");
            $stmt->bind_param("ii", $client_id, $product_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar del carrito']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    exit();
}
