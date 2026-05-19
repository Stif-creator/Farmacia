<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

actualizarEstadosVentas($conexion);

$idVenta = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];

$ventaCheck = $conexion->prepare('SELECT estado_venta, fecha FROM ventas WHERE id_venta = ? AND id_usuario = ? LIMIT 1');
$ventaCheck->bind_param('ii', $idVenta, $idUsuario);
$ventaCheck->execute();
$resultadoVenta = $ventaCheck->get_result();
$venta = $resultadoVenta->fetch_assoc();

if ($venta) {
    $estado = normalizarEstadoVenta($venta['estado_venta']);
    $fechaVenta = new DateTime($venta['fecha']);
    $limiteCancelar = clone $fechaVenta;
    $limiteCancelar->add(new DateInterval('PT30S'));
    $ahora = new DateTime();

    if ($estado !== 'pendiente') {
        header('Location: mis_compras.php');
        exit;
    }

    if ($ahora > $limiteCancelar) {
        $updateRealizada = $conexion->prepare('UPDATE ventas SET estado_venta = ? WHERE id_venta = ?');
        $estadoRealizada = 'realizada';
        $updateRealizada->bind_param('si', $estadoRealizada, $idVenta);
        $updateRealizada->execute();
        header('Location: mis_compras.php');
        exit;
    }

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
    $estadoCancelada = 'cancelada';
    $updateVenta->bind_param('si', $estadoCancelada, $idVenta);
    $updateVenta->execute();

    $conexion->commit();
    header('Location: mis_compras.php?toast=compra_cancelada');
    exit;
}

header('Location: mis_compras.php');
exit;
