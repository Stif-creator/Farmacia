<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $check = $conexion->prepare('SELECT COUNT(*) AS total FROM productos WHERE id_marca = ?');
    $check->bind_param('i', $id);
    $check->execute();
    $total = $check->get_result()->fetch_assoc()['total'];

    if (intval($total) === 0) {
        $delete = $conexion->prepare('DELETE FROM marcas WHERE id_marca = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
    }
}

header('Location: marcas.php');
exit;