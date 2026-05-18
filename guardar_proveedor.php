<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($nombre !== '' && $telefono !== '' && $correo !== '' && $direccion !== '') {
    $insert = $conexion->prepare('INSERT INTO proveedores (nombre, telefono, correo, direccion) VALUES (?, ?, ?, ?)');
    $insert->bind_param('ssss', $nombre, $telefono, $correo, $direccion);
    $insert->execute();
}

header('Location: proveedores.php');
exit;