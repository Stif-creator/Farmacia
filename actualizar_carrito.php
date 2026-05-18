<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$accion = $_GET['accion'] ?? '';
$idUsuario = $_SESSION['id_usuario'];

if ($idProducto > 0 && in_array($accion, ['mas', 'menos'], true)) {
    $consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
    $estadoActivo = 'activo';
    $consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
    $consultaCarrito->execute();
    $resultadoCarrito = $consultaCarrito->get_result();
    $carritoActivo = $resultadoCarrito->fetch_assoc();

    if ($carritoActivo) {
        $idCarrito = $carritoActivo['id_carrito'];
        $queryProducto = $conexion->prepare('SELECT stock, precio FROM productos WHERE id_producto = ? LIMIT 1');
        $queryProducto->bind_param('i', $idProducto);
        $queryProducto->execute();
        $resultadoProducto = $queryProducto->get_result();
        $producto = $resultadoProducto->fetch_assoc();

        if ($producto) {
            $consultaDetalle = $conexion->prepare('SELECT cantidad FROM detalle_carrito WHERE id_carrito = ? AND id_producto = ? LIMIT 1');
            $consultaDetalle->bind_param('ii', $idCarrito, $idProducto);
            $consultaDetalle->execute();
            $resultadoDetalle = $consultaDetalle->get_result();
            $detalle = $resultadoDetalle->fetch_assoc();

            $cantidad = intval($detalle['cantidad'] ?? 0);
            if ($detalle) {
                if ($accion === 'mas' && $cantidad < intval($producto['stock'])) {
                    $cantidad++;
                    $update = $conexion->prepare('UPDATE detalle_carrito SET cantidad = ?, precio_unitario = ? WHERE id_carrito = ? AND id_producto = ?');
                    $update->bind_param('idii', $cantidad, $producto['precio'], $idCarrito, $idProducto);
                    $update->execute();
                } elseif ($accion === 'menos') {
                    if ($cantidad > 1) {
                        $cantidad--;
                        $update = $conexion->prepare('UPDATE detalle_carrito SET cantidad = ? WHERE id_carrito = ? AND id_producto = ?');
                        $update->bind_param('iii', $cantidad, $idCarrito, $idProducto);
                        $update->execute();
                    } else {
                        $delete = $conexion->prepare('DELETE FROM detalle_carrito WHERE id_carrito = ? AND id_producto = ?');
                        $delete->bind_param('ii', $idCarrito, $idProducto);
                        $delete->execute();
                        $cantidad = 0;
                    }
                }
                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = [];
                }
                if ($cantidad > 0) {
                    $_SESSION['carrito'][$idProducto] = $cantidad;
                } else {
                    unset($_SESSION['carrito'][$idProducto]);
                }
            }
        }
    }
}
header('Location: carrito.php');
exit;
