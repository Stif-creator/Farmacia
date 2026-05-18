<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$idProveedor = intval($_POST['id_proveedor'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

if ($idProveedor > 0 && $nombre !== '' && $telefono !== '' && $correo !== '' && $direccion !== '') {
    $update = $conexion->prepare('UPDATE proveedores SET nombre = ?, telefono = ?, correo = ?, direccion = ? WHERE id_proveedor = ?');
    $update->bind_param('ssssi', $nombre, $telefono, $correo, $direccion, $idProveedor);
    $update->execute();
}

header('Location: proveedores.php');
exit;