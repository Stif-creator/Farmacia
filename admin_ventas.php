<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$ventas = $conexion->query('SELECT v.*, u.nombre AS cliente FROM ventas v JOIN usuarios u ON v.id_usuario = u.id_usuario ORDER BY v.fecha DESC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Gestión de ventas</h2>
            <p class="text-secondary">Todas las ventas registradas en la tienda.</p>
        </div>
        <?php if (empty($ventas)): ?>
            <div class="alert alert-warning">No hay ventas registradas aún.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th># Venta</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?= $venta['id_venta'] ?></td>
                                <td><?= htmlspecialchars($venta['cliente']) ?></td>
                                <td><?= htmlspecialchars($venta['fecha']) ?></td>
                                <td>$ <?= number_format($venta['total'], 2, ',', '.') ?></td>
                                <td><span class="badge <?= $venta['estado_venta'] === 'Cancelada' ? 'badge-inactivo' : 'badge-activo' ?>"><?= htmlspecialchars($venta['estado_venta']) ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="detalle_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-secondary">Detalle</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php';
