<?php
require 'conexionbdd.php';

session_start();

if (!isset($_SESSION['id'])) {
    header('location:../login-sesion/login.php?error_message=Por favor inicie sesión');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['id']);
    $nombre_empresa = $_POST['nombre_empresa'];
    $rif = $_POST['rif'];
    $telefono_empresa = $_POST['telefono_empresa'];
    $direccion = $_POST['direccion'];
    $nombre_encargado = $_POST['nombre_contacto'];
    $cedula_encargado = $_POST['cedula_encargado'];
    $telefono_encargado = $_POST['telefono_encargado'];
    $correo = $_POST['correo_empresa'];
    $contrasena = $_POST['password'];
    $usuario_bloqueado = isset($_POST['Usuario_bloqueado']) ? intval($_POST['Usuario_bloqueado']) : 0;

    if ($cliente_id <= 0) {
        header('location:../login-sesion/login.php?error_message=ID de cliente inválido');
        exit();
    }

    // Determinar el estado del cliente y los intentos
    if ($usuario_bloqueado == 3) {
        $estado_cliente = 'Inactivo';
        $intentos = 3; // Número de intentos para bloquear al cliente
    } else {
        $estado_cliente = 'Activo';
        $intentos = 0; // Reiniciar intentos si el cliente está activo
    }

    // Preparar la consulta SQL
    $query = $conn->prepare("
        UPDATE clientes 
        SET 
            nombre_empresa = ?, 
            rif = ?, 
            telefono_empresa = ?, 
            direccion = ?, 
            nombre_encargado = ?, 
            cedula_encargado = ?, 
            telefono_encargado = ?, 
            correo = ?, 
            contrasena = ?, 
            estado_cliente = ?, 
            intentos = ? 
        WHERE id = ?
    ");

    // Ejecutar la consulta
    $query->bind_param(
        "ssssssssssii",
        $nombre_empresa,
        $rif,
        $telefono_empresa,
        $direccion,
        $nombre_encargado,
        $cedula_encargado,
        $telefono_encargado,
        $correo,
        $contrasena,
        $estado_cliente,
        $intentos,
        $cliente_id
    );

    if ($query->execute()) {
        // Redirigir con mensaje de éxito
        header('location:../panelAdmin/editar-cliente.php?id=' . $cliente_id . '&success_message=Cliente actualizado correctamente');
        exit();
    } else {
        // Redirigir con mensaje de error
        header('location:../panelAdmin/editar-cliente.php?id=' . $cliente_id . '&error_message=Error al actualizar el cliente');
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir con mensaje de error
    header('location:../login-sesion/login.php?error_message=Método no permitido');
    exit();
}
?>