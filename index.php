<?php
require_once 'auth.php';
require_once 'conexion.php';

$categoriaSeleccionada = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
$marcaSeleccionada = isset($_GET['marca']) ? intval($_GET['marca']) : 0;
$busqueda = trim($_GET['q'] ?? '');
$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
$estadoSeleccionado = $_GET['estado'] ?? 'todos';
if (!in_array($estadoSeleccionado, ['todos', 'activo', 'inactivo'], true)) {
    $estadoSeleccionado = 'todos';
}
$condiciones = [];
$tipos = '';
$valores = [];

if ($esAdmin) {
    if ($estadoSeleccionado !== 'todos') {
        $condiciones[] = 'p.estado = ?';
        $tipos .= 's';
        $valores[] = $estadoSeleccionado;
    }
} else {
    $condiciones[] = "p.estado = 'activo'";
}

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
$totalProductos = $resultado->num_rows;

$categoriasSql = $esAdmin ? 'SELECT * FROM categorias ORDER BY nombre_categoria ASC' : "SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre_categoria ASC";
$marcasSql = $esAdmin ? 'SELECT * FROM marcas ORDER BY nombre_marca ASC' : "SELECT * FROM marcas WHERE estado = 'activo' ORDER BY nombre_marca ASC";
$categorias = $conexion->query($categoriasSql)->fetch_all(MYSQLI_ASSOC);
$marcas = $conexion->query($marcasSql)->fetch_all(MYSQLI_ASSOC);

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
                <?php if ($esAdmin): ?>
                    <input type="hidden" name="estado" value="<?= htmlspecialchars($estadoSeleccionado) ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <input type="text" id="buscadorProductos" class="form-control" name="q" autocomplete="off" placeholder="Busca productos, marcas, categorías o proveedores" value="<?= htmlspecialchars($busqueda) ?>">
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
                <div class="card card-dashboard feature-card benefit-card p-4 text-center">
                    <div class="mb-3 icono">✓</div>
                    <h5 class="mb-2">Entrega rápida</h5>
                    <p class="mb-0 texto-pequeno">Recibe tu pedido con total seguridad en pocos días.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-dashboard feature-card benefit-card p-4 text-center">
                    <div class="mb-3 icono">🌿</div>
                    <h5 class="mb-2">Productos confiables</h5>
                    <p class="mb-0 texto-pequeno">Medicamentos y cuidado personal seleccionados por expertos.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-dashboard feature-card benefit-card p-4 text-center">
                    <div class="mb-3 icono">🛍️</div>
                    <h5 class="mb-2">Compra segura</h5>
                    <p class="mb-0 texto-pequeno">Tu carrito y datos protegidos con buenas prácticas web.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div>
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                    <a href="crear_producto.php" class="btn btn-gradient btn-lg shadow-sm"><i class="bi bi-plus-circle-fill me-2"></i>Agregar Producto</a>
                <?php endif; ?>
                <span class="text-secondary small">Mostrando <strong id="totalProductos"><?= $totalProductos ?></strong> producto<?= $totalProductos === 1 ? '' : 's' ?>.</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-light text-dark">Filtrar por categoría:</span>
                <a href="#" data-categoria="0" class="categoria-filtro badge <?= $categoriaSeleccionada === 0 ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Todas</a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="#" data-categoria="<?= $cat['id_categoria'] ?>" class="categoria-filtro badge <?= $categoriaSeleccionada === intval($cat['id_categoria']) ? 'bg-primary text-white' : 'bg-light text-dark' ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php if ($esAdmin): ?>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-light text-dark">Estado:</span>
                    <a href="#" data-estado="todos" class="estado-filtro badge <?= $estadoSeleccionado === 'todos' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Todos</a>
                    <a href="#" data-estado="activo" class="estado-filtro badge <?= $estadoSeleccionado === 'activo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Activos</a>
                    <a href="#" data-estado="inactivo" class="estado-filtro badge <?= $estadoSeleccionado === 'inactivo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Inactivos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<section id="catalogo" class="mb-5">
    <div class="section-titulo">
        <h2>Productos destacados</h2>
        <p class="text-secondary">Explora nuestra tienda de farmacia con productos seguros y promociones exclusivas.</p>
    </div>
    <div id="productosLoader" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
        <p class="mt-3 text-secondary">Actualizando productos...</p>
    </div>
    <div id="productosContenedor" class="row g-4">
        <?php if ($resultado->num_rows === 0): ?>
            <div class="col-12">
                <div class="alert alert-warning">No se encontraron productos con esa búsqueda.</div>
            </div>
        <?php endif; ?>
        <?php while ($producto = $resultado->fetch_assoc()):
            $stock = intval($producto['stock']);
            $stockClass = $stock === 0 ? 'bg-danger text-white' : ($stock <= 10 ? 'bg-warning text-dark' : 'bg-success text-white');
            $stockLabel = $stock === 0 ? 'Sin stock' : ($stock <= 10 ? 'Poco stock' : 'Stock alto');
        ?>
            <div class="col-md-6 col-lg-4 producto-item">
                <div class="card card-producto overflow-hidden h-100 hover-card">
                    <img src="<?= htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia') ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
                    <div class="card-body d-flex flex-column">
                        <span class="badge <?= $producto['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo' ?> mb-2"><?= htmlspecialchars(ucfirst($producto['estado'])) ?></span>
                        <span class="badge <?= $stockClass ?> mb-2"><?= htmlspecialchars($stockLabel) ?></span>
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
                            <?php elseif (!isset($_SESSION['rol'])): ?>
                                <a href="login.php?login_required=1" class="btn btn-farmacia"><i class="bi bi-cart-plus me-1"></i>Inicia sesión para comprar</a>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                                <div class="d-flex gap-2">
                                    <a href="editar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <?php if ($producto['estado'] === 'activo'): ?>
                                        <a href="eliminar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-danger flex-fill" onclick="return confirm('Desactivar este producto?');"><i class="bi bi-toggle-off me-1"></i>Desactivar</a>
                                    <?php else: ?>
                                        <a href="activar_producto.php?id=<?= $producto['id_producto'] ?>" class="btn btn-sm btn-success flex-fill"><i class="bi bi-toggle-on me-1"></i>Activar</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>
<script>
    const buscador = document.querySelector('#buscadorProductos');
    const contenedorProductos = document.querySelector('#productosContenedor');
    const loaderProductos = document.querySelector('#productosLoader');
    const totalProductosLabel = document.querySelector('#totalProductos');
    const filtrosCategoria = document.querySelectorAll('.categoria-filtro');
    const filtrosEstado = document.querySelectorAll('.estado-filtro');
    let debounceTimer = null;
    let categoriaSeleccionadaAjax = <?= intval($categoriaSeleccionada) ?>;
    let estadoSeleccionadoAjax = '<?= htmlspecialchars($estadoSeleccionado, ENT_QUOTES, 'UTF-8') ?>';

    function mostrarLoaderProductos(show) {
        if (!loaderProductos) return;
        loaderProductos.classList.toggle('d-none', !show);
    }

    async function filtrarProductos(categoria) {
        categoriaSeleccionadaAjax = categoria;
        if (!contenedorProductos) return;
        mostrarLoaderProductos(true);
        try {
            const term = buscador.value.trim();
            const params = new URLSearchParams({ categoria, estado: estadoSeleccionadoAjax });
            if (term.length >= 2) {
                params.set('q', term);
            }
            const respuesta = await fetch('filtrar_productos_ajax.php?' + params.toString());
            if (!respuesta.ok) throw new Error('Error de servidor');
            const datos = await respuesta.json();
            if (typeof datos.html !== 'string') throw new Error('Respuesta inválida');
            contenedorProductos.innerHTML = datos.html;
            if (totalProductosLabel) {
                totalProductosLabel.textContent = datos.total;
            }
            document.querySelectorAll('.categoria-filtro').forEach(el => {
                const cat = el.dataset.categoria;
                el.classList.toggle('bg-primary', String(cat) === String(categoria));
                el.classList.toggle('text-white', String(cat) === String(categoria));
                el.classList.toggle('bg-light', String(cat) !== String(categoria));
                el.classList.toggle('text-dark', String(cat) !== String(categoria));
            });
            document.querySelectorAll('.estado-filtro').forEach(el => {
                const estado = el.dataset.estado;
                el.classList.toggle('bg-primary', String(estado) === String(estadoSeleccionadoAjax));
                el.classList.toggle('text-white', String(estado) === String(estadoSeleccionadoAjax));
                el.classList.toggle('bg-light', String(estado) !== String(estadoSeleccionadoAjax));
                el.classList.toggle('text-dark', String(estado) !== String(estadoSeleccionadoAjax));
            });
        } catch (error) {
            contenedorProductos.innerHTML = '<div class="col-12"><div class="alert alert-danger">No se pudieron cargar los productos. Intenta nuevamente.</div></div>';
        } finally {
            mostrarLoaderProductos(false);
        }
    }

    if (buscador) {
        buscador.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                filtrarProductos(categoriaSeleccionadaAjax);
            }, 250);
        });
    }

    filtrosCategoria.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            const categoria = element.dataset.categoria || '0';
            filtrarProductos(categoria);
        });
    });

    filtrosEstado.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            estadoSeleccionadoAjax = element.dataset.estado || 'todos';
            const estadoInput = document.querySelector('input[name="estado"]');
            if (estadoInput) estadoInput.value = estadoSeleccionadoAjax;
            filtrarProductos(categoriaSeleccionadaAjax);
        });
    });
</script>
<?php include 'footer.php';
