<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];

if ($idProducto > 0) {
    $consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
    $estadoActivo = 'activo';
    $consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
    $consultaCarrito->execute();
    $resultadoCarrito = $consultaCarrito->get_result();
    $carritoActivo = $resultadoCarrito->fetch_assoc();

    if ($carritoActivo) {
        $idCarrito = $carritoActivo['id_carrito'];
        $delete = $conexion->prepare('DELETE FROM detalle_carrito WHERE id_carrito = ? AND id_producto = ?');
        $delete->bind_param('ii', $idCarrito, $idProducto);
        $delete->execute();
    }
}
if (isset($_SESSION['carrito'][$idProducto])) {
    unset($_SESSION['carrito'][$idProducto]);
}
header('Location: carrito.php');
exit;
