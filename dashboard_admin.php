<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$totalVendido = $conexion->query("SELECT COALESCE(SUM(total), 0) AS total FROM ventas")->fetch_assoc()['total'];
$cantidadVentas = $conexion->query("SELECT COUNT(*) AS total FROM ventas")->fetch_assoc()['total'];
$usuariosRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$productosRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'];
$bajoStock = $conexion->query("SELECT COUNT(*) AS total FROM productos WHERE stock <= 5")->fetch_assoc()['total'];
$categoriasRegistradas = $conexion->query("SELECT COUNT(*) AS total FROM categorias")->fetch_assoc()['total'];
$marcasRegistradas = $conexion->query("SELECT COUNT(*) AS total FROM marcas")->fetch_assoc()['total'];
$proveedoresRegistrados = $conexion->query("SELECT COUNT(*) AS total FROM proveedores")->fetch_assoc()['total'];
$carritosActivos = $conexion->query("SELECT COUNT(*) AS total FROM carrito WHERE estado = 'activo'")->fetch_assoc()['total'];

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
