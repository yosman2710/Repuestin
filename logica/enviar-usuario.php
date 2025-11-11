<?php

// Ensure no output is sent before this point
ob_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

session_start();
$nombre_empresa = $_GET['nombre_empresa'];
$correo_empresa = trim($_GET['correo_empresa']);
$nombre_encargado = $_GET['nombre_encargado'];
$contraseña = $_GET['contrasena'];

// Validate email address
if (!filter_var($correo_empresa, FILTER_VALIDATE_EMAIL)) {
    $error_message = urlencode("Correo electrónico no válido.". $correo_empresa);
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();
}

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tiramealgo_admin@gmail.com';
    $mail->Password = 'sdvw pmjg pnjm igpm';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('tiramealgo_admin@gmail.com', 'TirameAlgo Repuestos');
    $mail->addAddress($correo_empresa, $nombre_empresa);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->Subject = 'Generación de Usuario';

    // HTML body
    $mail->Body = '
    <html>
    <head>
        <style>
            .container {
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .header {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 20px;
            }
            .content {
                font-size: 16px;
                line-height: 1.5;
            }
            .footer {
                margin-top: 20px;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">Generación de Usuario</div>
            <div class="content">
                <p>Hola '. $nombre_encargado. ',</p>
                <p>Hemos generado un usuario para '. $nombre_empresa.', con el cual podrá acceder a nuestro catálogo de productos.<br></p>
                <p><b>Usuario:</b> ' . $correo_empresa . '</p>
                <p><b>Contraseña:</b> ' . $contraseña . '</p>
                <p><br>Por favor, no compartir esta información.<br></p>
            </div>
            <div class="footer">
                <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                <p>&copy; 2025 TirameAlgo Repuestos</p>
            </div>
        </div>
    </body>
    </html>';

    // Plain text body
    $mail->AltBody = 'Hola ' . $nombre_encargado . ', Hemos generado un usuario para '. $nombre_empresa.', con el cual podrá acceder a nuestro catálogo de productos. Usuario: ' . $correo_empresa . ' Contrasena: ' . $contraseña . ' Por favor, no comparta esta información.';

    $mail->send();
    $success_message = urlencode("Cliente registrado con éxito.");
    header("Location:../panelAdmin/agregarCliente.php?success_message=" . $success_message);
    exit();

} catch (Exception $e) {
    $error_message = urlencode("No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}");
    header("Location:../panelAdmin/agregarCliente.php?error_message=" . $error_message);
    exit();
}

// Ensure no output is sent before this point
ob_end_flush();
?>