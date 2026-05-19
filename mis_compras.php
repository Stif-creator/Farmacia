<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

actualizarEstadosVentas($conexion);

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
            <div class="mb-4 d-flex flex-column flex-md-row justify-content-between gap-3">
                <input id="misComprasSearch" data-search-table=".table-responsive table" type="text" class="form-control" placeholder="Buscar compras por ID, fecha o estado">
            </div>
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
                        <?php while ($venta = $resultado->fetch_assoc()):
                            $estadoVenta = normalizarEstadoVenta($venta['estado_venta']);
                            $fechaTimestamp = strtotime($venta['fecha']);
                            $segundosRestantes = $fechaTimestamp ? max(0, ($fechaTimestamp + 30) - time()) : 0;
                            $expiraEn = $fechaTimestamp ? ($fechaTimestamp + 30) * 1000 : 0;
                            $puedeCancelar = $estadoVenta === 'pendiente' && $segundosRestantes > 0;
                        ?>
                            <tr <?= $puedeCancelar ? 'data-venta-pendiente="1" data-expira="' . $expiraEn . '"' : '' ?>>
                                <td><?= $venta['id_venta'] ?></td>
                                <td data-fecha="<?= htmlspecialchars($venta['fecha']) ?>"><?= htmlspecialchars($venta['fecha']) ?></td>
                                <td>$ <?= number_format($venta['total'], 2, ',', '.') ?></td>
                                <td><span class="badge <?= obtenerClaseEstadoVenta($estadoVenta) ?>"><?= mostrarTextoEstadoVenta($estadoVenta) ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="detalle_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye me-1"></i>Detalles</a>
                                    <?php if ($puedeCancelar): ?>
                                        <a href="cancelar_compra.php?id=<?= $venta['id_venta'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseas cancelar esta compra?');"><i class="bi bi-x-circle me-1"></i>Cancelar</a>
                                        <div class="small text-secondary mt-2 compra-countdown" data-expira="<?= $expiraEn ?>">Puedes cancelar esta compra por <span><?= $segundosRestantes ?></span> segundos.</div>
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
<script>
    (function () {
        var countdowns = document.querySelectorAll('.compra-countdown[data-expira]');
        if (!countdowns.length) return;

        function updateCountdowns() {
            var shouldReload = false;
            countdowns.forEach(function (countdown) {
                var expiresAt = parseInt(countdown.dataset.expira || '0', 10);
                var seconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
                var label = countdown.querySelector('span');
                if (label) label.textContent = seconds;
                if (seconds <= 0) shouldReload = true;
            });
            if (shouldReload) {
                window.location.reload();
            }
        }

        updateCountdowns();
        setInterval(updateCountdowns, 1000);
    })();
</script>
<?php include 'footer.php';
