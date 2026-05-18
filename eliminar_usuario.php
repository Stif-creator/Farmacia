<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idUsuario > 0 && $idUsuario !== $_SESSION['id_usuario']) {
    $check = $conexion->prepare('SELECT COUNT(*) AS ventas FROM ventas WHERE id_usuario = ?');
    $check->bind_param('i', $idUsuario);
    $check->execute();
    $result = $check->get_result()->fetch_assoc();
    if (intval($result['ventas']) === 0) {
        $delete = $conexion->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $delete->bind_param('i', $idUsuario);
        $delete->execute();
    }
}
header('Location: admin_usuarios.php');
exit;
