<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $check = $conexion->prepare('SELECT COUNT(*) AS total FROM productos WHERE id_proveedor = ?');
    $check->bind_param('i', $id);
    $check->execute();
    $total = $check->get_result()->fetch_assoc()['total'];

    if (intval($total) === 0) {
        $delete = $conexion->prepare('DELETE FROM proveedores WHERE id_proveedor = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
    }
}

header('Location: proveedores.php');
exit;