<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$idCategoria = intval($_POST['id_categoria'] ?? 0);
$nombre = trim($_POST['nombre_categoria'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($idCategoria > 0 && $nombre !== '' && $descripcion !== '') {
    $update = $conexion->prepare('UPDATE categorias SET nombre_categoria = ?, descripcion = ? WHERE id_categoria = ?');
    $update->bind_param('ssi', $nombre, $descripcion, $idCategoria);
    $update->execute();
}
header('Location: categorias.php');
exit;
