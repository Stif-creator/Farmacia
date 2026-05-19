<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

actualizarEstadosVentas($conexion);

$ventas = $conexion->query('SELECT v.*, u.nombre AS cliente, u.correo AS correo FROM ventas v JOIN usuarios u ON v.id_usuario = u.id_usuario ORDER BY v.fecha DESC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Gestión de ventas</h2>
            <p class="text-secondary">Todas las ventas registradas en la tienda.</p>
        </div>
        <?php if (empty($ventas)): ?>
            <div class="alert alert-warning"><i class="bi bi-exclamation-circle me-2"></i>No hay ventas registradas aún.</div>
        <?php else: ?>
            <div class="card p-4 shadow-sm mb-4">
                <h5 class="mb-3"><i class="bi bi-funnel me-2"></i>Filtrar ventas</h5>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label small text-muted">Buscar por cliente, fecha o ID</label>
                        <input id="adminVentasSearch" data-search-table=".table-responsive table" data-date-from="#adminVentasFrom" data-date-to="#adminVentasTo" type="text" class="form-control" placeholder="Ej: Juan Pérez, 2026-05-19, 15">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small text-muted">Desde</label>
                        <input id="adminVentasFrom" type="date" class="form-control" title="Filtrar desde esta fecha">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label small text-muted">Hasta</label>
                        <input id="adminVentasTo" type="date" class="form-control" title="Filtrar hasta esta fecha">
                    </div>
                </div>
                <div class="mt-3">
                    <button id="clearFiltersBtn" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('adminVentasSearch').value=''; document.getElementById('adminVentasFrom').value=''; document.getElementById('adminVentasTo').value=''; document.getElementById('adminVentasSearch').dispatchEvent(new Event('input'));"><i class="bi bi-arrow-clockwise me-1"></i>Limpiar filtros</button>
                </div>
            </div>
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
                        <?php foreach ($ventas as $venta):
                            $estadoVenta = normalizarEstadoVenta($venta['estado_venta']);
                            $fechaTimestamp = strtotime($venta['fecha']);
                            $expiraEn = $fechaTimestamp ? ($fechaTimestamp + 30) * 1000 : 0;
                            $pendienteVigente = $estadoVenta === 'pendiente' && $fechaTimestamp && (($fechaTimestamp + 30) > time());
                        ?>
                            <tr <?= $pendienteVigente ? 'class="venta-pendiente-autoreload" data-expira="' . $expiraEn . '"' : '' ?>>
                                <td><?= $venta['id_venta'] ?></td>
                                <td><?= htmlspecialchars($venta['cliente']) ?> <span class="visually-hidden"><?= htmlspecialchars($venta['correo']) ?></span></td>
                                <td data-fecha="<?= htmlspecialchars($venta['fecha']) ?>"><?= htmlspecialchars($venta['fecha']) ?></td>
                                <td>$ <?= number_format($venta['total'], 2, ',', '.') ?></td>
                                <td><span class="badge <?= obtenerClaseEstadoVenta($estadoVenta) ?>"><?= mostrarTextoEstadoVenta($estadoVenta) ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="detalle_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-secondary">Detalles</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    (function () {
        var pendingRows = document.querySelectorAll('.venta-pendiente-autoreload[data-expira]');
        if (!pendingRows.length) return;
        var nextExpiration = Array.prototype.reduce.call(pendingRows, function (nearest, row) {
            var expiresAt = parseInt(row.dataset.expira || '0', 10);
            return nearest === 0 || (expiresAt > 0 && expiresAt < nearest) ? expiresAt : nearest;
        }, 0);
        if (nextExpiration > 0) {
            setTimeout(function () {
                window.location.reload();
            }, Math.max(1000, nextExpiration - Date.now() + 300));
        }
    })();
</script>
<?php include 'footer.php';
