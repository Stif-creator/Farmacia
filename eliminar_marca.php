<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $estado = 'inactivo';
    $update = $conexion->prepare('UPDATE marcas SET estado = ? WHERE id_marca = ?');
    $update->bind_param('si', $estado, $id);
    $update->execute();
}

header('Location: marcas.php');
exit;
