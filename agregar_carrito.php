<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];
if ($idProducto > 0) {
    $query = $conexion->prepare('SELECT stock, precio FROM productos WHERE id_producto = ? LIMIT 1');
    $query->bind_param('i', $idProducto);
    $query->execute();
    $resultado = $query->get_result();
    $producto = $resultado->fetch_assoc();

    if ($producto && $producto['stock'] > 0) {
        $estadoActivo = 'activo';
        $consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
        $consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
        $consultaCarrito->execute();
        $resultadoCarrito = $consultaCarrito->get_result();
        $carritoActivo = $resultadoCarrito->fetch_assoc();

        if (!$carritoActivo) {
            $insertCarrito = $conexion->prepare('INSERT INTO carrito (id_usuario, estado) VALUES (?, ?)');
            $insertCarrito->bind_param('is', $idUsuario, $estadoActivo);
            $insertCarrito->execute();
            $idCarrito = $conexion->insert_id;
        } else {
            $idCarrito = $carritoActivo['id_carrito'];
        }

        $consultaDetalle = $conexion->prepare('SELECT cantidad FROM detalle_carrito WHERE id_carrito = ? AND id_producto = ? LIMIT 1');
        $consultaDetalle->bind_param('ii', $idCarrito, $idProducto);
        $consultaDetalle->execute();
        $resultadoDetalle = $consultaDetalle->get_result();
        $detalle = $resultadoDetalle->fetch_assoc();

        $cantidadActual = intval($detalle['cantidad'] ?? 0);
        $cantidadNueva = min($cantidadActual + 1, intval($producto['stock']));

        if ($detalle) {
            $updateDetalle = $conexion->prepare('UPDATE detalle_carrito SET cantidad = ?, precio_unitario = ? WHERE id_carrito = ? AND id_producto = ?');
            $updateDetalle->bind_param('idii', $cantidadNueva, $producto['precio'], $idCarrito, $idProducto);
            $updateDetalle->execute();
        } else {
            $insertDetalle = $conexion->prepare('INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
            $insertDetalle->bind_param('iiid', $idCarrito, $idProducto, $cantidadNueva, $producto['precio']);
            $insertDetalle->execute();
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }
        $_SESSION['carrito'][$idProducto] = $cantidadNueva;
    }
}
header('Location: carrito.php');
exit;
