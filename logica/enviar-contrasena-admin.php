<?php
ob_start(); 
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repuestos_johbri";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el correo electrónico del parámetro GET
$email = isset($_GET['email']) ? $_GET['email'] : '';

if (empty($email)) {
    header("Location: ../login-sesion/olvidarContraseña.php?error_message=" . urlencode("El correo electrónico es requerido."));
    exit();
}

// Verificar si el correo electrónico existe en la base de datos
$sql = "SELECT nombre_administrador, correo, contrasena FROM administrador WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // El correo electrónico existe, obtener los datos relacionados
    $user = $result->fetch_assoc();
    
    $nombre_administrador = $user['nombre_administrador'];
    $correo_empresa = $user['correo'];
    $contraseña = $user['contrasena'];

    // Enviar correo electrónico con la contraseña

    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'johbrirepuestos@gmail.com';
        $mail->Password = 'sdvw pmjg pnjm igpm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('johbrirepuestos@gmail.com', 'Johbri Repuestos');
        $mail->addAddress($correo_empresa, $nombre_empresa);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->Subject = 'Recuperación de Contraseña';

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
                .info {
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">Recuperación de Contraseña</div>
                <div class="content">
                    <p>Hola '. $nombre_administrador. ',</p>
                    <p>Hemos recuperado su contraseña para su cuenta, con el cual podrá acceder a su cuenta.<br></p>
                    <p><b>Usuario:</b> ' . $correo_empresa . '</p>
                    <p><b>Contraseña:</b> ' . $contraseña . '</p>
                    <p><br>Por favor, no comparta esta información.<br></p>
                </div>
                <div class="footer">
                    <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
                    <p>&copy; 2025 Johbri Repuestos</p>
                </div>
            </div>
        </body>
        </html>';

        // Plain text body
        $mail->AltBody = 'Hola ' . $nombre_administrador . ', Hemos recuperado su contraseña para su cuenta, con el cual podrá acceder a su cuenta. Usuario: ' . $correo_empresa . ' Contraseña: ' . $contraseña . ' Por favor, no comparta esta información. Esta es una notificación automática.';

        $mail->send();
        $success_message = urlencode("Contraseña enviada con éxito.");
        header("Location: ../login-sesion/login.php?success_message=" . $success_message);
        exit();

    } catch (Exception $e) {
        $error_message = urlencode("No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}");
        header("Location: ../login-sesion/login.php?error_message=" . $error_message);
        exit();
    }

    ob_end_flush();

} else {
    header("Location: ../login-sesion/login.php?error_message=" . urlencode("El correo electrónico no está registrado."));
}

$stmt->close();
$conn->close();

ob_end_flush();
?>