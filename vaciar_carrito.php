<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];
$consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
$estadoActivo = 'activo';
$consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
$consultaCarrito->execute();
$resultadoCarrito = $consultaCarrito->get_result();
$carritoActivo = $resultadoCarrito->fetch_assoc();

if ($carritoActivo) {
    $idCarrito = $carritoActivo['id_carrito'];
    $update = $conexion->prepare('UPDATE carrito SET estado = ? WHERE id_carrito = ?');
    $estadoVaciado = 'vaciado';
    $update->bind_param('si', $estadoVaciado, $idCarrito);
    $update->execute();

    $delete = $conexion->prepare('DELETE FROM detalle_carrito WHERE id_carrito = ?');
    $delete->bind_param('i', $idCarrito);
    $delete->execute();
}

unset($_SESSION['carrito']);
header('Location: carrito.php');
exit;
