<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');
$input = json_decode(file_get_contents('php://input'), true);
$idProducto = isset($input['id']) ? intval($input['id']) : 0;
$accion = $input['accion'] ?? '';
$idUsuario = $_SESSION['id_usuario'];

if ($idProducto <= 0 || !in_array($accion, ['mas','menos'], true)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
    exit;
}

$consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
$estadoActivo = 'activo';
$consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
$consultaCarrito->execute();
$resultadoCarrito = $consultaCarrito->get_result();
$carritoActivo = $resultadoCarrito->fetch_assoc();

if (!$carritoActivo) {
    echo json_encode(['success' => false, 'message' => 'Carrito no encontrado.']);
    exit;
}
$idCarrito = $carritoActivo['id_carrito'];

$queryProducto = $conexion->prepare('SELECT stock, precio FROM productos WHERE id_producto = ? LIMIT 1');
$queryProducto->bind_param('i', $idProducto);
$queryProducto->execute();
$resultadoProducto = $queryProducto->get_result();
$producto = $resultadoProducto->fetch_assoc();
if (!$producto) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado.']);
    exit;
}

$consultaDetalle = $conexion->prepare('SELECT cantidad FROM detalle_carrito WHERE id_carrito = ? AND id_producto = ? LIMIT 1');
$consultaDetalle->bind_param('ii', $idCarrito, $idProducto);
$consultaDetalle->execute();
$resultadoDetalle = $consultaDetalle->get_result();
$detalle = $resultadoDetalle->fetch_assoc();
$cantidad = intval($detalle['cantidad'] ?? 0);

if ($accion === 'mas') {
    if ($cantidad < intval($producto['stock'])) {
        $cantidad++;
        $update = $conexion->prepare('UPDATE detalle_carrito SET cantidad = ? WHERE id_carrito = ? AND id_producto = ?');
        $update->bind_param('iii', $cantidad, $idCarrito, $idProducto);
        $update->execute();
    }
} else {
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

// Recalcular subtotal y total
$consulta = $conexion->prepare('SELECT dc.cantidad, dc.id_producto, p.precio FROM detalle_carrito dc JOIN productos p ON dc.id_producto = p.id_producto WHERE dc.id_carrito = ?');
$consulta->bind_param('i', $idCarrito);
$consulta->execute();
$result = $consulta->get_result();
$total = 0.0;
$subtotal = 0.0;
while ($row = $result->fetch_assoc()) {
    $total += intval($row['cantidad']) * floatval($row['precio']);
    if ($row['id_producto'] == $idProducto) {
        $subtotal = intval($row['cantidad']) * floatval($row['precio']);
    }
}

// If we deleted the product, subtotal may be 0; if it's 0 ensure returned value is 0
if ($cantidad === 0) $subtotal = 0.0;

// Also compute subtotal by fetching product price if missing
if ($subtotal === 0 && $cantidad > 0) {
    $subtotal = $cantidad * floatval($producto['precio']);
}

// format numbers
echo json_encode(['success' => true, 'cantidad' => $cantidad, 'subtotal' => round($subtotal,2), 'total' => round($total,2)]);
