<?php
require 'conexionbdd.php';
session_start();

if(!ISSET($_SESSION['id'])){
    header('location:../login-sesion/login.php');
    exit();
}

// Update the query to use id_orden instead of id
$sql = "UPDATE ordenes SET estado = 'rechazada' WHERE id_orden = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_GET['id']);

if($stmt->execute()){
    header('location: ../panelAdmin/ordenes.php?success=La orden ha sido rechazada');
} else {
    header('location: ../panelAdmin/ordenes.php?error=Error al rechazar la orden');
}

$stmt->close();
$conn->close();
?>