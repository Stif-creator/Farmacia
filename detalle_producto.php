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

$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
if (($producto['estado'] ?? 'activo') !== 'activo' && !$esAdmin) {
    include 'header.php';
    ?>
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-circle me-2"></i>Producto no disponible.
            </div>
            <a href="index.php" class="btn btn-outline-secondary">Volver al catalogo</a>
        </div>
    </div>
    <?php
    include 'footer.php';
    exit;
}

$imagenesProducto = [];
if (!empty($producto['imagen'])) {
    $imagenesProducto[] = $producto['imagen'];
}
$imagenesQuery = $conexion->prepare('SELECT ruta FROM producto_imagenes WHERE id_producto = ? ORDER BY orden ASC, id_imagen ASC');
$imagenesQuery->bind_param('i', $id);
$imagenesQuery->execute();
$imagenesResultado = $imagenesQuery->get_result();
while ($imagenFila = $imagenesResultado->fetch_assoc()) {
    if (!in_array($imagenFila['ruta'], $imagenesProducto, true)) {
        $imagenesProducto[] = $imagenFila['ruta'];
    }
}
if (empty($imagenesProducto)) {
    $imagenesProducto[] = 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';
}

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="card card-producto overflow-hidden">
            <div class="row g-0">
                <div class="col-md-6">
                    <?php if (count($imagenesProducto) > 1): ?>
                        <div id="productoImagenesCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                            <div class="carousel-inner h-100">
                                <?php foreach ($imagenesProducto as $index => $imagenProducto): ?>
                                    <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($imagenProducto) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="img-fluid w-100 producto-gallery-img">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#productoImagenesCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productoImagenesCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($imagenesProducto[0]) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="img-fluid w-100 producto-gallery-img">
                    <?php endif; ?>
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
                        <p class="mb-4">Stock disponible: <strong id="stockDisponible"><?= intval($producto['stock']) ?></strong></p>
                        <div class="d-flex flex-column gap-2">
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <div class="d-flex align-items-center gap-2">
                                    <button id="btnMinus" class="btn btn-outline-secondary btn-sm"><i class="bi bi-dash-lg"></i></button>
                                    <input id="cantidadSelector" type="number" min="1" value="1" class="form-control text-center" style="width:80px;" />
                                    <button id="btnPlus" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-lg"></i></button>
                                </div>
                                <button id="btnAgregar" data-id="<?= $producto['id_producto'] ?>" class="btn btn-farmacia btn-lg">Agregar al carrito</button>
                                <a href="agregar_favorito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-farmacia btn-lg">Agregar a favoritos</a>
                            <?php endif; ?>
                            <?php if ($esAdmin): ?>
                                <div class="d-flex gap-2">
                                    <a href="editar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-secondary">Editar</a>
                                    <?php if ($producto['estado'] === 'activo'): ?>
                                        <a href="eliminar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-danger" onclick="return confirm('Desactivar este producto?');">Desactivar</a>
                                    <?php else: ?>
                                        <a href="activar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-success">Activar</a>
                                    <?php endif; ?>
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
<?php include 'footer.php'; ?>

<script>
    (function(){
        const stockEl = document.getElementById('stockDisponible');
        const btnMinus = document.getElementById('btnMinus');
        const btnPlus = document.getElementById('btnPlus');
        const inputQty = document.getElementById('cantidadSelector');
        const btnAgregar = document.getElementById('btnAgregar');
        const maxStock = stockEl ? parseInt(stockEl.textContent) : 1;
        if (btnMinus && inputQty) {
            btnMinus.addEventListener('click', ()=>{
                const v = Math.max(1, parseInt(inputQty.value || 1) - 1);
                inputQty.value = v;
            });
        }
        if (btnPlus && inputQty) {
            btnPlus.addEventListener('click', ()=>{
                const v = Math.min(maxStock, parseInt(inputQty.value || 1) + 1);
                inputQty.value = v;
            });
        }
        if (btnAgregar) {
            btnAgregar.addEventListener('click', async ()=>{
                const id = btnAgregar.dataset.id;
                const qty = Math.max(1, Math.min(maxStock, parseInt(inputQty.value || 1)));
                try{
                    const resp = await fetch('agregar_carrito.php', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({id: id, qty: qty})
                    });
                    // soportar redirección si el endpoint devuelve HTML
                    if (resp.redirected) {
                        window.location = resp.url;
                        return;
                    }
                    // si éxito, mostrar pequeño toast y actualizar contador si existe
                    // simple feedback
                    if (resp.ok) {
                        const container = document.createElement('div');
                        container.className = 'toast align-items-center text-bg-success border-0 show';
                        container.style.position = 'fixed';
                        container.style.right = '20px';
                        container.style.bottom = '20px';
                        container.style.zIndex = 9999;
                        container.innerHTML = '<div class="d-flex"><div class="toast-body">Producto agregado al carrito</div><button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentNode.parentNode.remove()"></button></div>';
                        document.body.appendChild(container);
                        setTimeout(()=>container.remove(), 3000);
                    }
                }catch(e){console.error(e);}
            });
        }
    })();
</script>
