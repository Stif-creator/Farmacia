<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = 0;
$input = null;
// aceptar id por GET o JSON POST
if (isset($_GET['id'])) $idProducto = intval($_GET['id']);
else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['id'])) $idProducto = intval($input['id']);
}
// cantidad opcional
$cantidadSolicitada = 1;
if (isset($_GET['qty'])) $cantidadSolicitada = max(1, intval($_GET['qty']));
else if (!empty($input['qty'])) $cantidadSolicitada = max(1, intval($input['qty']));

$idUsuario = $_SESSION['id_usuario'];
if ($idProducto > 0) {
    $query = $conexion->prepare('SELECT stock, precio, estado FROM productos WHERE id_producto = ? LIMIT 1');
    $query->bind_param('i', $idProducto);
    $query->execute();
    $resultado = $query->get_result();
    $producto = $resultado->fetch_assoc();

    if ($producto && $producto['estado'] === 'activo' && $producto['stock'] > 0) {
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
        $cantidadNueva = min($cantidadActual + $cantidadSolicitada, intval($producto['stock']));

        if ($detalle) {
            $updateDetalle = $conexion->prepare('UPDATE detalle_carrito SET cantidad = ? WHERE id_carrito = ? AND id_producto = ?');
            $updateDetalle->bind_param('iii', $cantidadNueva, $idCarrito, $idProducto);
            $updateDetalle->execute();
        } else {
            $insertDetalle = $conexion->prepare('INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)');
            $insertDetalle->bind_param('iii', $idCarrito, $idProducto, $cantidadNueva);
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
