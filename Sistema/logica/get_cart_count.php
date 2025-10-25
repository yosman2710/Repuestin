<?php
require 'conexionbdd.php';
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

$client_id = $_SESSION['id'];
$sql = "SELECT COUNT(DISTINCT producto_id) as unique_count FROM carrito WHERE cliente_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['count' => $row['unique_count']]);
?>