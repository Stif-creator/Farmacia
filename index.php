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
$totalProductos = $resultado->num_rows;

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
                <div class="mb-3 position-relative">
                    <input type="text" id="buscadorProductos" class="form-control" name="q" autocomplete="off" placeholder="Busca por producto, marca, categoría o proveedor" value="<?= htmlspecialchars($busqueda) ?>">
                    <div id="sugerenciasProductos" class="search-dropdown d-none"></div>
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
<script>
    const buscador = document.querySelector('#buscadorProductos');
    const sugerencias = document.querySelector('#sugerenciasProductos');
    const contenedorProductos = document.querySelector('#productosContenedor');
    const loaderProductos = document.querySelector('#productosLoader');
    const totalProductosLabel = document.querySelector('#totalProductos');
    const filtrosCategoria = document.querySelectorAll('.categoria-filtro');
    let debounceTimer = null;
    let categoriaSeleccionadaAjax = <?= intval($categoriaSeleccionada) ?>;

    function cerrarSugerencias() {
        if (sugerencias) {
            sugerencias.classList.add('d-none');
            sugerencias.innerHTML = '';
        }
    }

    function mostrarLoaderProductos(show) {
        if (!loaderProductos) return;
        loaderProductos.classList.toggle('d-none', !show);
    }

    async function filtrarProductos(categoria) {
        categoriaSeleccionadaAjax = categoria;
        if (!contenedorProductos) return;
        mostrarLoaderProductos(true);
        cerrarSugerencias();
        try {
            const term = buscador.value.trim();
            const params = new URLSearchParams({ categoria });
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
        } catch (error) {
            contenedorProductos.innerHTML = '<div class="col-12"><div class="alert alert-danger">No se pudieron cargar los productos. Intenta nuevamente.</div></div>';
        } finally {
            mostrarLoaderProductos(false);
        }
    }

    if (buscador && sugerencias) {
        buscador.addEventListener('input', () => {
            const valor = buscador.value.trim();
            clearTimeout(debounceTimer);
            if (valor.length < 2) {
                cerrarSugerencias();
                return;
            }
            debounceTimer = setTimeout(async () => {
                try {
                    const respuesta = await fetch('buscar_productos_ajax.php?q=' + encodeURIComponent(valor));
                    if (!respuesta.ok) throw new Error('Error al buscar');
                    const resultados = await respuesta.json();
                    if (!Array.isArray(resultados) || resultados.length === 0) {
                        sugerencias.innerHTML = '<div class="p-3 text-muted">No se encontraron productos.</div>';
                        sugerencias.classList.remove('d-none');
                        return;
                    }
                    sugerencias.innerHTML = resultados.map(item => `
                        <a href="detalle_producto.php?id=${item.id}" class="d-flex align-items-center gap-3 suggestion-item text-decoration-none text-dark p-3">
                            <img src="${item.imagen}" alt="${item.nombre}" class="suggestion-thumb rounded-3">
                            <div>
                                <div class="fw-semibold">${item.nombre}</div>
                                <div class="small text-muted">$${item.precio}</div>
                            </div>
                        </a>
                    `).join('');
                    sugerencias.classList.remove('d-none');
                } catch (error) {
                    cerrarSugerencias();
                }
            }, 300);
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('#sugerenciasProductos') && event.target !== buscador) {
                cerrarSugerencias();
            }
        });
    }

    filtrosCategoria.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            const categoria = element.dataset.categoria || '0';
            filtrarProductos(categoria);
        });
    });
</script>
<?php include 'footer.php';
