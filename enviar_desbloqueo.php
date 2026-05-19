<?php
require_once 'auth.php';
require_once 'conexion.php';
require_once 'vendor/autoload.php';
require_once 'config_correo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function redirect($url, $params=[]) {
    $qs = http_build_query($params);
    header('Location: ' . $url . ($qs ? '?' . $qs : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('solicitar_desbloqueo.php');
}

$correo = trim($_POST['correo'] ?? '');
$asunto = trim($_POST['asunto'] ?? 'Solicitud de desbloqueo de cuenta');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $mensaje === '') {
    $_SESSION['desbloqueo_error'] = 'Por favor completa todos los campos correctamente.';
    redirect('solicitar_desbloqueo.php');
}

// Enviar correo al administrador principal
$destino = defined('HARDCODED_ADMIN_EMAIL') ? HARDCODED_ADMIN_EMAIL : (SMTP_USER ?? null);
if (!$destino) {
    $_SESSION['desbloqueo_error'] = 'No hay administrador configurado para recibir solicitudes.';
    redirect('solicitar_desbloqueo.php');
}

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
    $mail->addAddress($destino, 'Administrador');
    $mail->addReplyTo($correo);
    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $body = '<h3>Solicitud de desbloqueo</h3>';
    $body .= '<p><strong>Correo solicitante:</strong> ' . htmlspecialchars($correo) . '</p>';
    $body .= '<p><strong>Mensaje:</strong><br>' . nl2br(htmlspecialchars($mensaje)) . '</p>';
    $mail->Body = $body;
    $mail->AltBody = strip_tags($body);
    $mail->send();
    $_SESSION['desbloqueo_exito'] = 'Solicitud enviada correctamente. El equipo de soporte revisará tu caso.';
} catch (Exception $e) {
    $_SESSION['desbloqueo_error'] = 'Error al enviar la solicitud. Intenta nuevamente más tarde.';
}

redirect('solicitar_desbloqueo.php');
