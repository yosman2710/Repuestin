<?php
require("conexionbdd.php");
require("validar.php");

session_start();

$flag = true;

//Parte 1: Agregar Producto

if (empty($_POST['num_parte'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese el número de parte");
    exit();
}

if (empty($_POST['nombre_producto'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese el nombre del producto");
    exit();
}

if (empty($_POST['categoria'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese la categoría del producto");
    exit();
}

if (empty($_POST['marca'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese la marca del producto");
    exit();
}

if (empty($_POST['precio'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese el precio del producto");
    exit();
}

if (empty($_POST['stock'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=Ingrese el stock del producto");
    exit();
}

if (!is_numeric($_POST['stock'])) {
    $flag = false;
    header("location: ../panelAdmin/agregar-producto.php?error_message=La cantidad del producto debe ser un tipo de dato numérico");
    exit();
}



$stmt = $conn->prepare("INSERT INTO productos (
    numero_de_parte, 
    nombre_producto, 
    categoria_producto, 
    marca_producto, 
    precio_producto, 
    stock_producto, 
    descripcion_producto, 
    fecha_creacion, 
    hora_creacion
) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), CURTIME())");

$stmt->bind_param(
    "ssssdis",
    $_POST['num_parte'], 
    $_POST['nombre_producto'], 
    $_POST['categoria'], 
    $_POST['marca'], 
    $_POST['precio'], 
    $_POST['stock'], 
    $_POST['descripcion']);
    
$stmt->execute();
$id_producto = $conn->insert_id;
echo $id_producto;



//Parte 2: Agregar Fotos
//Subir Foto 1

if (isset($_FILES['file-upload-1'])) {

    insertarFotos($_FILES['file-upload-1'], 1, $id_producto);

}

//Subir Foto 2
if (isset($_FILES['file-upload-2'])) {
    insertarFotos($_FILES['file-upload-2'], 2, $id_producto);
}

//Subir Foto 3
if (isset($_FILES['file-upload-3'])) {
    insertarFotos($_FILES['file-upload-3'], 3, $id_producto);
}

//Subir Foto 4
if (isset($_FILES['file-upload-4'])) {
    insertarFotos($_FILES['file-upload-4'], 4, $id_producto);
}
header("location: ../panelAdmin/agregar-producto.php?success_message=Producto agregado correctamente");

exit();


?>

