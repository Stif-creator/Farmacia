<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $estado = 'inactivo';
    $update = $conexion->prepare('UPDATE proveedores SET estado = ? WHERE id_proveedor = ?');
    $update->bind_param('si', $estado, $id);
    $update->execute();
}

header('Location: proveedores.php');
exit;
