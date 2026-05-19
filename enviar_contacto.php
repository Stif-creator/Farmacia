<?php
require_once 'auth.php';
protegerRuta();
require_once 'vendor/autoload.php';
require_once 'config_correo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$asunto = trim($_POST['asunto'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($asunto === '' || $categoria === '' || $mensaje === '') {
    $_SESSION['contacto_error'] = 'Todos los campos son obligatorios.';
    header('Location: contacto.php');
    exit;
}

$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
$correoUsuario = $_SESSION['correo'] ?? '';
$destino = 'admin@farmacia.com';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->addAddress($destino, 'Soporte Farmacia');
    $mail->Subject = 'Nuevo contacto - ' . $categoria . ': ' . $asunto;
    $mail->isHTML(true);
    $mail->Body = '<div style="font-family:Arial, sans-serif; color:#1f3d2e; padding:20px; background:#f3faf8; border-radius:18px;">
        <h2 style="color:#0f9d58; margin-bottom:0.5rem;">Nuevo mensaje de contacto</h2>
        <p><strong>Nombre:</strong> ' . htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Correo:</strong> ' . htmlspecialchars($correoUsuario, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Categoría:</strong> ' . htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Asunto:</strong> ' . htmlspecialchars($asunto, ENT_QUOTES, 'UTF-8') . '</p>
        <p><strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>
        <p style="margin-top:1rem; font-size:0.9rem; color:#4b6b67;">Fecha: ' . date('d/m/Y H:i') . '</p>
    </div>';
    $mail->AltBody = "Nuevo mensaje de contacto\nNombre: $nombreUsuario\nCorreo: $correoUsuario\nCategoría: $categoria\nAsunto: $asunto\nMensaje:\n$mensaje";
    $mail->send();
    $_SESSION['contacto_exito'] = 'Tu mensaje se ha enviado correctamente. Gracias por contactarnos.';
} catch (Exception $e) {
    $_SESSION['contacto_error'] = 'No se pudo enviar el mensaje. Revisa la configuración de correo o intenta más tarde.';
}

header('Location: contacto.php');
exit;
