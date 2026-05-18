<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$nombre = trim($_POST['nombre_categoria'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($nombre !== '' && $descripcion !== '') {
    $insert = $conexion->prepare('INSERT INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)');
    $insert->bind_param('ss', $nombre, $descripcion);
    $insert->execute();
}
header('Location: categorias.php');
exit;
