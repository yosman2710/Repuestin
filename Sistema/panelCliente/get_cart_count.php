<?php
session_start();
require '../logica/conexionbdd.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$client_id = $_SESSION['id'];

// Obtener el total de items en el carrito
$query = "SELECT SUM(cantidad) as total FROM carrito WHERE cliente_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => (int)($row['total'] ?? 0)]);
