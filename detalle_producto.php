<?php
require_once 'auth.php';
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$query = $conexion->prepare('SELECT p.*, c.nombre_categoria, m.nombre_marca AS nombre_marca, pr.nombre AS nombre_proveedor FROM productos p'
    . ' LEFT JOIN categorias c ON p.id_categoria = c.id_categoria'
    . ' LEFT JOIN marcas m ON p.id_marca = m.id_marca'
    . ' LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor'
    . ' WHERE p.id_producto = ? LIMIT 1');
$query->bind_param('i', $id);
$query->execute();
$resultado = $query->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto) {
    header('Location: index.php');
    exit;
}

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="card card-producto overflow-hidden">
            <div class="row g-0">
                <div class="col-md-6">
                    <img src="<?= htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="img-fluid w-100 h-100">
                </div>
                <div class="col-md-6">
                    <div class="card-body p-4">
                        <span class="badge <?= $producto['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo' ?> mb-3"><?= htmlspecialchars(ucfirst($producto['estado'])) ?></span>
                        <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
                        <p class="text-secondary mb-2">Marca: <?= htmlspecialchars($producto['nombre_marca'] ?? $producto['marca']) ?></p>
                        <p class="text-secondary mb-2">Categoría: <?= htmlspecialchars($producto['nombre_categoria']) ?></p>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <p class="text-secondary mb-2">Proveedor: <?= htmlspecialchars($producto['nombre_proveedor'] ?? 'N/A') ?></p>
                        <?php endif; ?>
                        <p class="mb-3"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
                        <h3 class="fw-bold mb-4">$ <?= number_format($producto['precio'], 2, ',', '.') ?></h3>
                        <p class="mb-4">Stock disponible: <strong><?= intval($producto['stock']) ?></strong></p>
                        <div class="d-flex flex-column gap-2">
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <a href="agregar_carrito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-farmacia btn-lg">Agregar al carrito</a>
                                <a href="agregar_favorito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-farmacia btn-lg">Agregar a favoritos</a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                                <div class="d-flex gap-2">
                                    <a href="editar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-secondary">Editar</a>
                                    <a href="eliminar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-danger" onclick="return confirm('Deseas eliminar este producto?');">Eliminar</a>
                                </div>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline-secondary">Volver al catálogo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php';
