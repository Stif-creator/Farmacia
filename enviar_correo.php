<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config_correo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCodigo2FA($correoDestino, $nombreDestino, $codigo) {
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
        $mail->addAddress($correoDestino, $nombreDestino);
        $mail->isHTML(true);
        $mail->Subject = 'Código 2FA - Farmacia SaludPlus';
        $mail->Body = '<div style="font-family:Arial, sans-serif; color:#1f3d2e;">
            <div style="background:#f3faf8; padding:20px; border-radius:18px; border:1px solid #d7f0e0;">
                <h2 style="margin-bottom:0.5rem; color:#0f9d58;">Farmacia SaludPlus</h2>
                <p style="margin:0 0 1rem; color:#4b6b67;">Hola ' . htmlspecialchars($nombreDestino, ENT_QUOTES, 'UTF-8') . ',</p>
                <p style="margin:0 0 1rem;">Te enviamos tu código de verificación para acceder al sistema de farmacia. Usa el código a continuación:</p>
                <div style="background:#ffffff; padding:18px 22px; border-radius:16px; text-align:center; margin:1rem 0; border:1px solid #d7f0e0;">
                    <span style="font-size:32px; letter-spacing:4px; font-weight:700; color:#0f9d58;">' . htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') . '</span>
                </div>
                <p style="margin:0 0 0.5rem; color:#4b6b67;">Por tu seguridad, este código caduca en 5 minutos.</p>
                <p style="margin:0; color:#4b6b67;">Si no solicitaste este código, ignora este correo.</p>
                <div style="margin-top:2rem; padding:16px; background:#e0f7f4; border-radius:14px; color:#0f9d58;">
                    <strong>Farmacia SaludPlus</strong><br>
                    Compra segura y atención confiable.
                </div>
            </div>
        </div>';
        $mail->AltBody = 'Farmacia SaludPlus\nTu código 2FA es: ' . $codigo . '\nCaduca en 5 minutos.';
        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
