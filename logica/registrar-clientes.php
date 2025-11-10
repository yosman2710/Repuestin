<?php

require 'conexionbdd.php';	
require 'validar.php';

session_start();

$flag =true;

$nombre_empresa = $_POST['nombre_empresa'];
$rif = $_POST['rif'];
$telefono_empresa = $_POST['telefono_empresa'];
$direccion = $_POST['direccion'];
$nombre_encargado = $_POST['nombre_contacto'];
$cedula_encargado = $_POST['cedula_encargado'];
$telefono_encargado = $_POST['telefono_encargado'];
$correo_empresa = $_POST['correo_empresa'];
$contraseña = $_POST['password'];

//Validar si el nombre de la empresa esta vacio
if (empty($nombre_empresa)) {
    
    $flag = false;
    $error_message = urlencode("El Nombre de la empresa no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//validar si el nombre de la empresa contiene caracteres especiales
 if (!verificarCadena($nombre_empresa)) {

    $flag = false;
    $error_message = urlencode("El Nombre de la empresa no puede contener caracteres especiales solo puede contener .");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el rif esta vacio
if (empty($rif)) {
    
    $flag = false;
    $error_message = urlencode("El RIF no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el rif es correcto
if (!validar_RIF($rif)) {

    $flag = false;
    $error_message = urlencode("El RIF no contiene el formato esperado.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

// Validar si el rif ya existe
if (buscarRIF($rif)) {

    $flag = false;
    $error_message = urlencode("El RIF ya existe.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el telefono de la empresa esta vacio
if (empty($telefono_empresa)) {
    
    $flag = false;
    $error_message = urlencode("El telefono de la empresa no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el telefono de la empresa es correcto
if (!validar_telefono($telefono_empresa)) {

    $flag = false;
    $error_message = urlencode("El telefono de la empresa no tiene el formato correcto.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si la direccion esta vacia
if (empty($direccion)) {
    
    $flag = false;
    $error_message = urlencode("La dirección no puede puede estar vacia.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el nombre del encargado esta vacio
if (empty($nombre_encargado)) {
    
    $flag = false;
    $error_message = urlencode("El Nombre del encargado no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//validar si el nombre del encargado contiene caracteres especiales
if (!verificarCadena($nombre_encargado)) {

    $flag = false;
    $error_message = urlencode("El Nombre del encargado no puede contener caracteres especiales.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si la cedula del encargado esta vacia
if (empty($cedula_encargado)) {
    
    $flag = false;
    $error_message = urlencode("La cedula del encargado no puede puede estar vacia.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si la cedula del encargado es correcta
if (!validar_cedula($cedula_encargado)) {
    
    $flag = false;
    $error_message = urlencode("La cedula del encargado no tiene el formato correcto.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}


//Validar si el telefono del encargado esta vacio   
if (empty($telefono_encargado)) {
    
    $flag = false;
    $error_message = urlencode("El telefono del encargado no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el telefono del encargado es correcto 
if (!validar_telefono($telefono_encargado)) {
    
    $flag = false;
    $error_message = urlencode("El telefono del encargado no tiene el formato correcto.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el correo de la empresa esta vacio
if (empty($correo_empresa)) {
    
    $flag = false;
    $error_message = urlencode("El correo de la empresa no puede puede estar vacio.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si el correo de la empresa es correcto
if (!filter_var($correo_empresa, FILTER_VALIDATE_EMAIL)) {
    
    $flag = false;
    $error_message = urlencode("El correo de la empresa no tiene el formato correcto.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si la contraseña esta vacia
if (empty($contraseña)) {
    
    $flag = false;
    $error_message = urlencode("La contraseña no puede puede estar vacia.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

//Validar si la contraseña tiene al menos 8 caracteres
if (!validated_password($contraseña)) {
    
    $flag = false;
    $error_message = urlencode("La contraseña debe tener al menos 8 caracteres, 1 caracter especial, 1 mayuscula, 1 número.");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();

}

if ($flag) {

    $sql = "INSERT INTO clientes (nombre_empresa, rif, telefono_empresa, direccion, nombre_encargado, cedula_encargado, telefono_encargado, correo, contrasena) VALUES ('$nombre_empresa', '$rif', '$telefono_empresa', '$direccion', '$nombre_encargado', '$cedula_encargado', '$telefono_encargado', '$correo_empresa', '$contraseña')";

    if ($conn->query($sql) === TRUE) {
        $query_params = http_build_query([
            'nombre_empresa' => $nombre_empresa,
            'rif' => $rif,
            'telefono_empresa' => $telefono_empresa,
            'direccion' => $direccion,
            'nombre_encargado' => $nombre_encargado,
            'cedula_encargado' => $cedula_encargado,
            'telefono_encargado' => $telefono_encargado,
            'correo_empresa' => $correo_empresa,
            'contrasena' => $contraseña
        ]);
        header("Location: enviar-usuario.php?$query_params");

        // $error_message = urlencode("Cliente registrado con éxito.");
        // header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
        // exit();
    } else {
        $error_message = urlencode("Error: " . $sql . "<br>" . $conn->error);
        header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
        exit();
    }

}

$conn->close();


?>