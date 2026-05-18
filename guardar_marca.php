<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$nombre = trim($_POST['nombre_marca'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($nombre !== '' && $descripcion !== '') {
    $insert = $conexion->prepare('INSERT INTO marcas (nombre_marca, descripcion) VALUES (?, ?)');
    $insert->bind_param('ss', $nombre, $descripcion);
    $insert->execute();
}

header('Location: marcas.php');
exit;