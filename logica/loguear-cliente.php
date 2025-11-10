<?php
require 'conexionbdd.php';
require 'validar.php';

session_start();

$flag = true;

$username = $_POST['username'];
$password = $_POST['password'];


//Verificar si los campos estan vacios
if(empty($username)){

    $flag = false;
    $error_message = urlencode("Debe ingresar su correo electronico.");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();

}


if(empty($password)){

    $flag = false;
    $error_message = urlencode("Debe ingresar su contraseña.");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();

}

//verificar Correo electronico
if (!EmailVa($username)){

    $flag = false;
    $error_message = urlencode("Formato correo electronico invalido");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();

}

//verificar si el correo existe
if (!buscarAdmin($username, "clientes")){

    $flag = false;
    $error_message = urlencode("Correo electronico no registrado porfavor comuniquese con el administrador");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();

}

//Verificar si la contraseña contiene lo esperado
if (!validated_password($password)){

    $flag = false;
    $error_message = urlencode("La contraseña debe tener al menos 8 caracteres e incluir una combinación de letras mayúsculas y minúsculas, números y caracteres especiales.");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();

}


if (!verificarContrasena($password, "clientes",$username)){

    $flag = false;

}

if ($flag == true) {
    $sql = "SELECT id FROM clientes WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id_cliente = $row["id"];
        
        $_SESSION['time'] = time();
        $_SESSION['id'] = $id_cliente;
        header("Location: ../panelCliente/cliente.php");
        exit();

    } else {

        $error_message = urlencode("Error desconocido. Por favor, intente nuevamente.");
        header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
        exit();
    }
} 

else {
    $error_message = urlencode("Error desconocido. Por favor, intente nuevamente.");
    header("Location: ../login-sesion/loginCliente.php?error_message=" . $error_message);
    exit();
}

?>