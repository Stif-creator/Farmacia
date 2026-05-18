<?php
require_once 'auth.php';
require_once 'conexion.php';

$categoriaSeleccionada = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$marcaSeleccionada = isset($_GET['marca']) ? intval($_GET['marca']) : 0;
$busqueda = trim($_GET['q'] ?? '');
$condiciones = ["p.estado = 'activo'"];
$tipos = '';
$valores = [];

if ($categoriaSeleccionada > 0) {
    $condiciones[] = 'p.id_categoria = ?';
    $tipos .= 'i';
    $valores[] = $categoriaSeleccionada;
}
if ($marcaSeleccionada > 0) {
    $condiciones[] = 'p.id_marca = ?';
    $tipos .= 'i';
    $valores[] = $marcaSeleccionada;
}
if ($busqueda !== '') {
    $condiciones[] = '(p.nombre LIKE ? OR p.marca LIKE ? OR c.nombre_categoria LIKE ? OR m.nombre_marca LIKE ? OR pr.nombre LIKE ?)';
    $termino = '%' . $busqueda . '%';
    $tipos .= 'sssss';
    $valores[] = $termino;
    $valores[] = $termino;
    $valores[] = $termino;
    $valores[] = $termino;
    $valores[] = $termino;
}

$sql = 'SELECT p.*, c.nombre_categoria, m.nombre_marca AS nombre_marca, pr.nombre AS nombre_proveedor FROM productos p'
    . ' LEFT JOIN categorias c ON p.id_categoria = c.id_categoria'
    . ' LEFT JOIN marcas m ON p.id_marca = m.id_marca'
    . ' LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor';
if (count($condiciones)) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}
$sql .= ' ORDER BY p.id_producto DESC';

$consulta = $conexion->prepare($sql);
if ($tipos) {
    $bindNames = [];
    $bindNames[] = &$tipos;
    foreach ($valores as $key => $value) {
        $bindNames[] = &$valores[$key];
    }
    call_user_func_array([$consulta, 'bind_param'], $bindNames);
}
$consulta->execute();
$resultado = $consulta->get_result();

$categorias = $conexion->query('SELECT * FROM categorias ORDER BY nombre_categoria ASC')->fetch_all(MYSQLI_ASSOC);
$marcas = $conexion->query('SELECT * FROM marcas ORDER BY nombre_marca ASC')->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>
<div class="hero-banner row align-items-center gx-4 py-5 mb-5">
    <div class="hero-decor"></div>
    <div class="col-lg-7 text-white">
        <h1 class="display-5 fw-bold">Farmacia SaludPlus</h1>
        <p class="lead mb-4">Medicamentos, cuidado personal y productos de salud al alcance de un clic</p>
        <a href="#catalogo" class="btn btn-light btn-lg">Explorar productos</a>
    </div>
    <div class="col-lg-5 mt-4 mt-lg-0 text-center">
        <img src="https://placehold.co/600x420/16a34a/ffffff?text=Farmacia+SaludPlus" alt="Farmacia SaludPlus" class="img-fluid hero-image shadow-lg rounded-4">
    </div>
</div>

<div class="row mb-5">
    <div class="col-lg-4 mb-4">
        <div class="card card-categoria p-4 h-100">
            <h3 class="h5">Busca productos</h3>
            <form method="get" action="index.php">
                <div class="mb-3">
                    <input type="text" class="form-control" name="q" placeholder="Nombre, marca o categoría" value="<?= htmlspecialchars($busqueda) ?>">
                </div>
                <div class="mb-3">
                    <select class="form-select" name="categoria">
                        <option value="0">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" <?= $categoriaSeleccionada === intval($cat['id_categoria']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <select class="form-select" name="marca">
                        <option value="0">Todas las marcas</option>
                        <?php foreach ($marcas as $marca): ?>
                            <option value="<?= $marca['id_marca'] ?>" <?= $marcaSeleccionada === intval($marca['id_marca']) ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-farmacia w-100">Buscar</button>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-dashboard p-4 text-center bg-white">
                    <div class="mb-3 icono">✓</div>
                    <h5 class="mb-2">Entrega rápida</h5>
                    <p class="mb-0 texto-pequeno">Recibe tu pedido con total seguridad en pocos días.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-dashboard p-4 text-center bg-white">
                    <div class="mb-3 icono">🌿</div>
                    <h5 class="mb-2">Productos confiables</h5>
                    <p class="mb-0 texto-pequeno">Medicamentos y cuidado personal seleccionados por expertos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-dashboard p-4 text-center bg-white">
                    <div class="mb-3 icono">🛍️</div>
                    <h5 class="mb-2">Compra segura</h5>
                    <p class="mb-0 texto-pequeno">Tu carrito y datos protegidos con buenas prácticas web.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<section id="catalogo" class="mb-5">
    <div class="section-titulo">
        <h2>Productos destacados</h2>
        <p class="text-secondary">Explora nuestra tienda de farmacia con productos seguros y promociones exclusivas.</p>
    </div>
    <div class="row g-4">
        <?php if ($resultado->num_rows === 0): ?>
            <div class="col-12">
                <div class="alert alert-warning">No se encontraron productos con esa búsqueda.</div>
            </div>
        <?php endif; ?>
        <?php while ($producto = $resultado->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-producto overflow-hidden h-100 hover-card">
                    <img src="<?= htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    <div class="card-body d-flex flex-column">
                        <span class="badge <?= $producto['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo' ?> mb-2"><?= htmlspecialchars(ucfirst($producto['estado'])) ?></span>
                        <h5 class="card-title"><?= htmlspecialchars($producto['nombre']) ?></h5>
                        <p class="texto-pequeno text-secondary mb-2">Marca <?= htmlspecialchars($producto['nombre_marca'] ?? $producto['marca']) ?></p>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin' && !empty($producto['nombre_proveedor'])): ?>
                            <p class="texto-pequeno text-secondary mb-2">Proveedor <?= htmlspecialchars($producto['nombre_proveedor']) ?></p>
                        <?php endif; ?>
                        <p class="card-text mb-3"><?= htmlspecialchars(substr($producto['descripcion'], 0, 85)) ?>...</p>
                        <p class="fw-bold mb-3">$ <?= number_format($producto['precio'], 2, ',', '.') ?></p>
                        <div class="mt-auto d-grid gap-2">
                            <a href="detalle_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-farmacia"><i class="bi bi-eye me-1"></i>Ver detalle</a>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente'): ?>
                                <a href="agregar_carrito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-farmacia"><i class="bi bi-cart-plus me-1"></i>Agregar al carrito</a>
                                <a href="agregar_favorito.php?id=<?= $producto['id_producto'] ?>" class="btn btn-outline-farmacia"><i class="bi bi-heart me-1"></i>Favorito</a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                                <div class="d-flex gap-2">
                                    <a href="editar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <a href="eliminar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-danger flex-fill" onclick="return confirm('Deseas eliminar este producto?');"><i class="bi bi-trash me-1"></i>Eliminar</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<?php include 'footer.php';
