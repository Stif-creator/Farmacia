<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$idMarca = intval($_POST['id_marca'] ?? 0);
$nombre = trim($_POST['nombre_marca'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($idMarca > 0 && $nombre !== '' && $descripcion !== '') {
    $update = $conexion->prepare('UPDATE marcas SET nombre_marca = ?, descripcion = ? WHERE id_marca = ?');
    $update->bind_param('ssi', $nombre, $descripcion, $idMarca);
    $update->execute();
}

header('Location: marcas.php');
exit;