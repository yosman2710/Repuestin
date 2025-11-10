<?php

include 'conexionbdd.php';
include 'validar.php';

$id = $_GET['id_producto'];
$id_producto = obtenerIdProducto($id);

// Eliminar fotos de la carpeta
$foto_path = "../assets/foto-repuestos/";
$foto_query = "SELECT ruta_foto FROM foto_productos WHERE id_producto = ?";
if ($stmt_foto = $conn->prepare($foto_query)) {
    $stmt_foto->bind_param("i", $id_producto);
    $stmt_foto->execute();
    $stmt_foto->bind_result($nombre_foto);
    while ($stmt_foto->fetch()) {
        $file = $nombre_foto;
        if (file_exists($file)) {

           
            if (!unlink($file)) {
                error_log("Error al eliminar la foto: " . $file);
            }
        } else {
            error_log("La foto no existe: " . $file);
        }
    }
    $stmt_foto->close();
}

$sql = "DELETE FROM foto_productos WHERE id_producto = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_producto);
    if ($stmt->execute()) {
        
    } else {
        
    }
    $stmt->close();
} else {
    
}

$sql_producto = "DELETE FROM productos WHERE id_producto = ?";

if ($stmt_producto = $conn->prepare($sql_producto)) {
    $stmt_producto->bind_param("i", $id_producto);
    if ($stmt_producto->execute()) {
        header("location: ../panelAdmin/ver-Producto.php?success_message=Producto eliminado correctamente");
        exit();
    } else {
        echo "Error al eliminar el producto: " . $stmt_producto->error;
    }
    $stmt_producto->close();
} else {
    echo "Error al preparar la consulta de producto: " . $conn->error;
}

$conn->close();

?>