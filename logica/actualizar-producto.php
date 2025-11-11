<?php

require 'conexionbdd.php';
require 'validar.php';

session_start();

$flag = true;
$numero_de_parte = $_POST['numero_de_parte_campo'];
$nombre_producto = $_POST['nombre_producto'];
$precio_producto = $_POST['precio_producto'];
$categoria_producto = $_POST['categoria_producto'];
$marca_producto = $_POST['marca_producto'];
$stock_producto = $_POST['stock_producto'];
$descripcion_producto = $_POST['descripcion_producto'];

// Verificar si los campos no están vacíos
if (empty($numero_de_parte)) {
    $error_message = urlencode("El campo numero de parte no puede estar vacio.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}

if (empty($nombre_producto)) {
    $flag = false;
    $error_message = urlencode("El campo nombre producto no puede estar vacio.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();

}

if (empty($precio_producto)) {
    $flag = false;
    $error_message = urlencode("El campo precio no puede estar vacio.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}

if (empty($categoria_producto)) {
    $flag = false;
    $error_message = urlencode("El campo categoria no puede estar vacio.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}

if (empty($marca_producto)) {
    $flag = false;
    $error_message = urlencode("El campo marca no puede estar vacio.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}

//verificar si el numero de parte ya existe
if ($numero_de_parte != $_SESSION['e_num_part']) {

    if (!buscarNumPart($numero_de_parte, 'productos')) {
        $flag = false;
        $error_message = urlencode("El numero de parte ya existe, Por favor intente con otro.");
        header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
        exit();

    }
}

if (!is_numeric($precio_producto)) {
    $flag = false;
    $error_message = urlencode("El precio y el stock deben ser numericos.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
    
}   

if($stock_producto < 0){
    $flag = false;
    $error_message = urlencode("El stock no puede ser negativo.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}  

if($stock_producto != (int)$stock_producto){
    $flag = false;
    $error_message = urlencode("El stock debe ser un numero entero.");
    header("Location: ../panelAdmin/editarProducto.php?numero_de_parte=" . $_SESSION['e_num_part'] . "&error_message=" . $error_message);
    exit();
}


if ($flag) {
    $conexion = new mysqli('localhost', 'root', '', 'repuestos_tiramealgo');
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    $sql = "UPDATE productos SET numero_de_parte = ?, nombre_producto = ?, categoria_producto = ?, marca_producto = ?, precio_producto = ?, stock_producto = ?, descripcion_producto = ? WHERE numero_de_parte = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssdiss", $numero_de_parte, $nombre_producto, $categoria_producto, $marca_producto, $precio_producto, $stock_producto, $descripcion_producto, $_SESSION['e_num_part']);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    header("Location: ../panelAdmin/ver-Producto.php");
    
    exit();
}

?>