<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];
$estadoActivo = 'activo';
$consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
$consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
$consultaCarrito->execute();
$resultadoCarrito = $consultaCarrito->get_result();
$carritoActivo = $resultadoCarrito->fetch_assoc();

if (!$carritoActivo) {
    header('Location: carrito.php');
    exit;
}

$idCarrito = $carritoActivo['id_carrito'];
$consulta = $conexion->prepare(
    'SELECT dc.id_producto, dc.cantidad, p.precio AS precio_actual, p.stock FROM detalle_carrito dc '
    . 'JOIN productos p ON dc.id_producto = p.id_producto '
    . 'WHERE dc.id_carrito = ? FOR UPDATE'
);
$consulta->bind_param('i', $idCarrito);
$conexion->begin_transaction();
$consulta->execute();
$resultado = $consulta->get_result();

$productos = [];
$total = 0.0;
while ($producto = $resultado->fetch_assoc()) {
    $cantidad = intval($producto['cantidad']);
    if ($cantidad <= 0 || $cantidad > intval($producto['stock'])) {
        $conexion->rollback();
        header('Location: carrito.php');
        exit;
    }
    $precioUnitario = floatval($producto['precio_actual']);
    $producto['subtotal'] = $cantidad * $precioUnitario;
    $producto['precio_unitario'] = $precioUnitario;
    $total += $producto['subtotal'];
    $productos[] = $producto;
}

if (empty($productos)) {
    $conexion->rollback();
    header('Location: carrito.php');
    exit;
}

$insertVenta = $conexion->prepare('INSERT INTO ventas (id_usuario, total, estado_venta, fecha) VALUES (?, ?, ?, ?)');
$estadoVenta = 'Completada';
$fecha = date('Y-m-d H:i:s');
$insertVenta->bind_param('idss', $idUsuario, $total, $estadoVenta, $fecha);
$insertVenta->execute();
$idVenta = $conexion->insert_id;

$insertDetalle = $conexion->prepare('INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)');
$updateStock = $conexion->prepare('UPDATE productos SET stock = stock - ? WHERE id_producto = ?');

foreach ($productos as $producto) {
    $insertDetalle->bind_param('iiid', $idVenta, $producto['id_producto'], $producto['cantidad'], $producto['precio_unitario']);
    $insertDetalle->execute();
    $updateStock->bind_param('ii', $producto['cantidad'], $producto['id_producto']);
    $updateStock->execute();
}

$updateCarrito = $conexion->prepare('UPDATE carrito SET estado = ? WHERE id_carrito = ?');
$estadoComprado = 'comprado';
$updateCarrito->bind_param('si', $estadoComprado, $idCarrito);
$updateCarrito->execute();

$conexion->commit();
unset($_SESSION['carrito']);
header('Location: mis_compras.php');
exit;
