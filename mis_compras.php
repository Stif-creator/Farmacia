<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];
$query = $conexion->prepare('SELECT * FROM ventas WHERE id_usuario = ? ORDER BY fecha DESC');
$query->bind_param('i', $idUsuario);
$query->execute();
$resultado = $query->get_result();

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Mis compras</h2>
            <p class="text-secondary">Historial de tus pedidos y su estado.</p>
        </div>
        <?php if ($resultado->num_rows === 0): ?>
            <div class="alert alert-warning">Aún no has realizado ninguna compra.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th># Venta</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($venta = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?= $venta['id_venta'] ?></td>
                                <td><?= htmlspecialchars($venta['fecha']) ?></td>
                                <td>$ <?= number_format($venta['total'], 2, ',', '.') ?></td>
                                <td><span class="badge <?= $venta['estado_venta'] === 'Cancelada' ? 'badge-inactivo' : 'badge-activo' ?>"><?= htmlspecialchars($venta['estado_venta']) ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="detalle_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye me-1"></i>Detalle</a>
                                    <?php if ($venta['estado_venta'] !== 'Cancelada'): ?>
                                        <a href="cancelar_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseas cancelar esta compra?');"><i class="bi bi-x-circle me-1"></i>Cancelar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php';
