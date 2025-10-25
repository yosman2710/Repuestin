<?php
require 'conexionbdd.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check admin login
    $stmt = $conn->prepare("SELECT * FROM administrador WHERE correo = ? AND contrasena = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Admin login successful
        $admin = $result->fetch_assoc();
        $_SESSION['id'] = $admin['id_administrador'];
        $_SESSION['nombre'] = $admin['nombre_administrador'];
        $_SESSION['cargo'] = $admin['cargo'];
        $_SESSION['tipo'] = 'admin';
        $_SESSION['time'] = time();
        header('Location: ../panelAdmin/admin.php');
        exit();
    } else {
        // Check client login
        $stmt = $conn->prepare("SELECT * FROM clientes WHERE correo = ? AND contrasena = ? AND estado_cliente = 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Client login successful
            $cliente = $result->fetch_assoc();
            $_SESSION['id'] = $cliente['id'];
            $_SESSION['nombre_empresa'] = $cliente['nombre_empresa'];
            $_SESSION['tipo'] = 'cliente';
            header('Location: ../panelCliente/cliente.php');
            exit();
        } else {
            // Login failed
            header('Location: ../login-sesion/login.php?error_message=' . urlencode('Credenciales incorrectas'));
            exit();
        }
    }
} else {
    header('Location: ../login-sesion/login.php?error_message=' . urlencode('Método no permitido'));
    exit();
}
?>