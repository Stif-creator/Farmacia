<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idUsuario = $_SESSION['id_usuario'];
$query = $conexion->prepare('SELECT f.id_favorito, p.* FROM favoritos f JOIN productos p ON f.id_producto = p.id_producto WHERE f.id_usuario = ?');
$query->bind_param('i', $idUsuario);
$query->execute();
$resultado = $query->get_result();

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-12">
        <div class="section-titulo">
            <h2>Mis favoritos</h2>
            <p class="text-secondary">Productos que guardaste para revisar más tarde.</p>
        </div>
        <?php if ($resultado->num_rows === 0): ?>
            <div class="alert alert-warning">No tienes productos en favoritos.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php while ($producto = $resultado->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-producto overflow-hidden h-100">
                            <img src="<?= htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                                <p class="texto-pequeno text-secondary mb-3">Marca <?= htmlspecialchars($producto['marca']) ?></p>
                                <p class="fw-bold mb-3">$ <?= number_format($producto['precio'], 2, ',', '.') ?></p>
                                <div class="mt-auto d-grid gap-2">
                                    <a href="detalle_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-farmacia"><i class="bi bi-eye me-1"></i>Ver detalle</a>
                                    <a href="agregar_carrito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-farmacia"><i class="bi bi-cart-plus me-1"></i>Agregar al carrito</a>
                                    <a href="quitar_favorito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Quitar favorito</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php';
