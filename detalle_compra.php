<?php
require_once 'auth.php';
require_once 'conexion.php';

$idVenta = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'] ?? 0;

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    $query = $conexion->prepare('SELECT * FROM ventas WHERE id_venta = ? LIMIT 1');
    $query->bind_param('i', $idVenta);
} else {
    $query = $conexion->prepare('SELECT * FROM ventas WHERE id_venta = ? AND id_usuario = ? LIMIT 1');
    $query->bind_param('ii', $idVenta, $idUsuario);
}
$query->execute();
$ventaResultado = $query->get_result();
$venta = $ventaResultado->fetch_assoc();

if (!$venta) {
    header('Location: mis_compras.php');
    exit;
}

$detalle = $conexion->prepare('SELECT dv.cantidad, dv.precio_unitario, p.nombre, p.marca FROM detalle_venta dv JOIN productos p ON dv.id_producto = p.id_producto WHERE dv.id_venta = ?');
$detalle->bind_param('i', $idVenta);
$detalle->execute();
$detalleResultado = $detalle->get_result();

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Detalle de la compra #<?= $venta['id_venta'] ?></h2>
            <p class="text-secondary">Revisa los productos incluidos en esta compra.</p>
        </div>
        <div class="card p-4 shadow-sm mb-4">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Fecha:</strong> <?= htmlspecialchars($venta['fecha']) ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Total:</strong> $ <?= number_format($venta['total'], 2, ',', '.') ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Estado:</strong> <?= htmlspecialchars($venta['estado_venta']) ?></p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $detalleResultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td><?= htmlspecialchars($item['marca']) ?></td>
                            <td><?= intval($item['cantidad']) ?></td>
                            <td>$ <?= number_format($item['precio_unitario'], 2, ',', '.') ?></td>
                            <td>$ <?= number_format($item['cantidad'] * $item['precio_unitario'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <a href="mis_compras.php" class="btn btn-outline-secondary">Volver a mis compras</a>
    </div>
</div>
<?php include 'footer.php';
