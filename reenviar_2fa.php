<?php
require_once 'auth.php';
require_once 'conexion.php';
require_once 'enviar_correo.php';

if (!isset($_SESSION['pendiente_2fa']) || $_SESSION['rol_pendiente'] === 'admin') {
    header('Location: login.php');
    exit;
}

$codigo = (string) random_int(100000, 999999);
$_SESSION['codigo_2fa_temp'] = $codigo;
$_SESSION['codigo_2fa_expira'] = time() + 300;
$correoDestino = $_SESSION['correo_pendiente'] ?? '';
$nombreDestino = $_SESSION['nombre_pendiente'] ?? 'Usuario';

$envio = false;
if ($_SESSION['id_pendiente'] > 0 && $correoDestino !== '') {
    $update = $conexion->prepare('UPDATE usuarios SET codigo_2fa = ? WHERE id_usuario = ?');
    $update->bind_param('si', $codigo, $_SESSION['id_pendiente']);
    $update->execute();
    $envio = enviarCodigo2FA($correoDestino, $nombreDestino, $codigo);
}

if ($envio) {
    $_SESSION['mensaje_2fa'] = 'Hemos reenviado el código a tu correo.';
} else {
    $_SESSION['error_2fa'] = 'No se pudo enviar el código. Revisa la configuración SMTP.';
}
header('Location: verificar_2fa.php');
exit;
