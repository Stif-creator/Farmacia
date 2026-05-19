<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

actualizarEstadosVentas($conexion);

$totalVendido = $conexion->query("SELECT COALESCE(SUM(total), 0) AS total FROM ventas")->fetch_assoc()['total'];
$cantidadVentas = $conexion->query("SELECT COUNT(*) AS total FROM ventas")->fetch_assoc()['total'];
$usuariosRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$productosRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'];
$bajoStock = $conexion->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 5")->fetch_assoc()['total'];
$categoriasRegistradas = $conexion->query("SELECT COUNT(*) AS total FROM categorias")->fetch_assoc()['total'];
$marcasRegistradas = $conexion->query("SELECT COUNT(*) AS total FROM marcas")->fetch_assoc()['total'];
$proveedoresRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM proveedores")->fetch_assoc()['total'];
$carritosActivos = $conexion->query("SELECT COUNT(*) AS total FROM carrito WHERE estado = 'activo'")->fetch_assoc()['total'];
$topProductos = $conexion->query("SELECT p.nombre, SUM(dv.cantidad) AS total_vendido FROM detalle_venta dv JOIN productos p ON dv.id_producto = p.id_producto GROUP BY dv.id_producto ORDER BY total_vendido DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$productosBajoStock = $conexion->query("SELECT nombre, stock FROM productos WHERE stock <= 5 ORDER BY stock ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>
<div class="row g-4 mb-5">
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Total vendido</h5>
            <p class="display-6 fw-bold">$ <?= number_format($totalVendido, 2, ',', '.') ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Ventas registradas</h5>
            <p class="display-6 fw-bold"><?= intval($cantidadVentas) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Usuarios registrados</h5>
            <p class="display-6 fw-bold"><?= intval($usuariosRegistrados) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Productos registrados</h5>
            <p class="display-6 fw-bold"><?= intval($productosRegistrados) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Marcas registradas</h5>
            <p class="display-6 fw-bold"><?= intval($marcasRegistradas) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Proveedores registrados</h5>
            <p class="display-6 fw-bold"><?= intval($proveedoresRegistrados) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Carritos activos</h5>
            <p class="display-6 fw-bold"><?= intval($carritosActivos) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Productos bajo stock</h5>
            <p class="display-6 fw-bold"><?= intval($bajoStock) ?></p>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card card-dashboard p-4">
            <h5>Categorías</h5>
            <p class="display-6 fw-bold"><?= intval($categoriasRegistradas) ?></p>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100">
            <h3>Top 5 productos más vendidos</h3>
            <?php if (empty($topProductos)): ?>
                <p class="text-secondary mt-3">Aún no hay ventas suficientes para mostrar los más vendidos.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush mt-3">
                    <?php foreach ($topProductos as $producto): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-3">
                            <span><?= htmlspecialchars($producto['nombre']) ?></span>
                            <span class="badge bg-primary rounded-pill"><?= intval($producto['total_vendido']) ?> vendidos</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100">
            <h3>Productos con bajo stock</h3>
            <?php if (empty($productosBajoStock)): ?>
                <p class="text-secondary mt-3">No hay productos con stock bajo por el momento.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush mt-3">
                    <?php foreach ($productosBajoStock as $producto): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-0 px-0 py-3">
                            <span><?= htmlspecialchars($producto['nombre']) ?></span>
                            <span class="badge bg-warning text-dark rounded-pill"><?= intval($producto['stock']) ?> unidades</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm">
            <h3>Accesos rápidos</h3>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <a href="admin_ventas.php" class="btn btn-outline-farmacia">Ver ventas</a>
                <a href="admin_usuarios.php" class="btn btn-outline-farmacia">Ver usuarios</a>
                <a href="categorias.php" class="btn btn-outline-farmacia">Gestionar categorías</a>
                <a href="crear_producto.php" class="btn btn-outline-farmacia">Crear producto</a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php';
