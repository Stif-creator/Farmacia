<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idVenta = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];

$ventaCheck = $conexion->prepare('SELECT estado_venta FROM ventas WHERE id_venta = ? AND id_usuario = ? LIMIT 1');
$ventaCheck->bind_param('ii', $idVenta, $idUsuario);
$ventaCheck->execute();
$resultadoVenta = $ventaCheck->get_result();
$venta = $resultadoVenta->fetch_assoc();

if ($venta && $venta['estado_venta'] !== 'Cancelada') {
    $conexion->begin_transaction();

    $detalle = $conexion->prepare('SELECT id_producto, cantidad FROM detalle_venta WHERE id_venta = ?');
    $detalle->bind_param('i', $idVenta);
    $detalle->execute();
    $resultadoDetalle = $detalle->get_result();

    $updateStock = $conexion->prepare('UPDATE productos SET stock = stock + ? WHERE id_producto = ?');
    while ($item = $resultadoDetalle->fetch_assoc()) {
        $updateStock->bind_param('ii', $item['cantidad'], $item['id_producto']);
        $updateStock->execute();
    }
    $updateVenta = $conexion->prepare('UPDATE ventas SET estado_venta = ? WHERE id_venta = ?');
    $estado = 'Cancelada';
    $updateVenta->bind_param('si', $estado, $idVenta);
    $updateVenta->execute();

    $conexion->commit();
}

header('Location: mis_compras.php');
exit;
