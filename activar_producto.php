<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $estado = 'activo';
    $update = $conexion->prepare('UPDATE productos SET estado = ? WHERE id_producto = ?');
    $update->bind_param('si', $estado, $id);
    $update->execute();
}

header('Location: index.php');
exit;
