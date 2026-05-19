<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];
$productos = [];
$total = 0.0;

$consultaCarrito = $conexion->prepare('SELECT id_carrito FROM carrito WHERE id_usuario = ? AND estado = ? LIMIT 1');
$estadoActivo = 'activo';
$consultaCarrito->bind_param('is', $idUsuario, $estadoActivo);
$consultaCarrito->execute();
$resultadoCarrito = $consultaCarrito->get_result();
$carritoActivo = $resultadoCarrito->fetch_assoc();

if ($carritoActivo) {
    $idCarrito = $carritoActivo['id_carrito'];
    $consulta = $conexion->prepare(
        'SELECT dc.id_producto, dc.cantidad, p.nombre, p.marca, p.imagen, p.precio AS precio_actual, p.stock, c.nombre_categoria, m.nombre_marca AS nombre_marca, pr.nombre AS nombre_proveedor '
        . 'FROM detalle_carrito dc '
        . 'JOIN productos p ON dc.id_producto = p.id_producto '
        . 'LEFT JOIN categorias c ON p.id_categoria = c.id_categoria '
        . 'LEFT JOIN marcas m ON p.id_marca = m.id_marca '
        . 'LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor '
        . 'WHERE dc.id_carrito = ?'
    );
    $consulta->bind_param('i', $idCarrito);
    $consulta->execute();
    $resultado = $consulta->get_result();
    while ($producto = $resultado->fetch_assoc()) {
        $producto['cantidad'] = intval($producto['cantidad']);
        $precioActual = floatval($producto['precio_actual'] ?? $producto['precio']);
        $producto['subtotal'] = $producto['cantidad'] * $precioActual;
        $producto['precio_actual'] = $precioActual;
        $total += $producto['subtotal'];
        $productos[] = $producto;
    }
}
include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Tu carrito</h2>
            <p class="text-secondary">Revisa tus productos antes de finalizar la compra.</p>
        </div>
        <?php if (empty($productos)): ?>
            <div class="alert alert-warning">Tu carrito está vacío.</div>
        <?php else: ?>
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($producto['nombre']) ?></strong><br>
                                    <small class="text-secondary">Marca <?= htmlspecialchars($producto['marca']) ?></small>
                                </td>
                                <td>$ <?= number_format($producto['precio_actual'], 2, ',', '.') ?></td>
                                <td>
                                    <div class="d-flex gap-1 align-items-center">
                                        <button class="btn btn-sm btn-outline-secondary btn-cantidad" data-id="<?= $producto['id_producto'] ?>" data-accion="menos"><i class="bi bi-dash-lg"></i></button>
                                        <span class="px-2 cantidad-valor" data-id="<?= $producto['id_producto'] ?>"><?= $producto['cantidad'] ?></span>
                                        <button class="btn btn-sm btn-outline-secondary btn-cantidad" data-id="<?= $producto['id_producto'] ?>" data-accion="mas"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                </td>
                                <td class="subtotal-td" data-id="<?= $producto['id_producto'] ?>">$ <?= number_format($producto['subtotal'], 2, ',', '.') ?></td>
                                <td><a href="quitar_carrito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Quitar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <a href="vaciar_carrito.php" class="btn btn-outline-danger"><i class="bi bi-trash-fill me-1"></i>Vaciar carrito</a>
                </div>
                <div class="text-end">
                    <p class="mb-1">Total</p>
                    <h3 id="totalCarrito">$ <?= number_format($total, 2, ',', '.') ?></h3>
                    <a href="finalizar_compra.php" class="btn btn-farmacia btn-lg mt-2"><i class="bi bi-credit-card-2-front-fill me-1"></i>Finalizar compra</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    async function actualizarCantidad(id, accion) {
        try {
            const resp = await fetch('actualizar_cantidad_carrito.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: id, accion: accion})
            });
            const datos = await resp.json();
            if (!datos.success) {
                alert(datos.message || 'Error al actualizar');
                return;
            }
            const cantidadEl = document.querySelector('.cantidad-valor[data-id="'+id+'"]');
            const subtotalEl = document.querySelector('.subtotal-td[data-id="'+id+'"]');
            if (cantidadEl) cantidadEl.textContent = datos.cantidad;
            if (subtotalEl) subtotalEl.textContent = '$ ' + parseFloat(datos.subtotal).toFixed(2).replace('.', ',');
            const totalEl = document.querySelector('#totalCarrito');
            if (totalEl) totalEl.textContent = '$ ' + parseFloat(datos.total).toFixed(2).replace('.', ',');
        } catch (e) {
            console.error(e);
        }
    }

    document.querySelectorAll('.btn-cantidad').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = btn.dataset.id;
            const accion = btn.dataset.accion;
            actualizarCantidad(id, accion);
        });
    });
</script>
<?php include 'footer.php';
