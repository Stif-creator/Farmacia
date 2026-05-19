<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$query = $conexion->prepare('SELECT * FROM productos WHERE id_producto = ? LIMIT 1');
$query->bind_param('i', $id);
$query->execute();
$resultado = $query->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto) {
    header('Location: index.php');
    exit;
}

$categoriasQuery = $conexion->prepare("SELECT * FROM categorias WHERE estado = 'activo' OR id_categoria = ? ORDER BY nombre_categoria ASC");
$categoriasQuery->bind_param('i', $producto['id_categoria']);
$categoriasQuery->execute();
$categorias = $categoriasQuery->get_result()->fetch_all(MYSQLI_ASSOC);

$marcasQuery = $conexion->prepare("SELECT * FROM marcas WHERE estado = 'activo' OR id_marca = ? ORDER BY nombre_marca ASC");
$marcasQuery->bind_param('i', $producto['id_marca']);
$marcasQuery->execute();
$marcas = $marcasQuery->get_result()->fetch_all(MYSQLI_ASSOC);

$proveedoresQuery = $conexion->prepare("SELECT * FROM proveedores WHERE estado = 'activo' OR id_proveedor = ? ORDER BY nombre ASC");
$proveedoresQuery->bind_param('i', $producto['id_proveedor']);
$proveedoresQuery->execute();
$proveedores = $proveedoresQuery->get_result()->fetch_all(MYSQLI_ASSOC);

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
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm auth-card">
            <h3 class="mb-3">Editar producto</h3>
            <form id="productoForm" action="actualizar_producto.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                <div class="row gy-3">
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Nombre</label>
                        <i class="bi bi-tag-fill"></i>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Categoría</label>
                        <i class="bi bi-list-ul"></i>
                        <select name="categoria" class="form-select" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <?php $categoriaInactiva = ($categoria['estado'] ?? 'activo') === 'inactivo'; ?>
                                <option value="<?= $categoria['id_categoria'] ?>" <?= $categoria['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre_categoria'] . ($categoriaInactiva ? ' (inactiva)' : '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Marca</label>
                        <i class="bi bi-award-fill"></i>
                        <select name="marca" class="form-select" required>
                            <option value="">Selecciona una marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <?php $marcaInactiva = ($marca['estado'] ?? 'activo') === 'inactivo'; ?>
                                <option value="<?= $marca['id_marca'] ?>" <?= $marca['id_marca'] == $producto['id_marca'] ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca'] . ($marcaInactiva ? ' (inactiva)' : '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Proveedor</label>
                        <i class="bi bi-people-fill"></i>
                        <select name="proveedor" class="form-select" required>
                            <option value="">Selecciona un proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <?php $proveedorInactivo = ($proveedor['estado'] ?? 'activo') === 'inactivo'; ?>
                                <option value="<?= $proveedor['id_proveedor'] ?>" <?= $proveedor['id_proveedor'] == $producto['id_proveedor'] ? 'selected' : '' ?>><?= htmlspecialchars($proveedor['nombre'] . ($proveedorInactivo ? ' (inactivo)' : '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Precio</label>
                        <i class="bi bi-currency-dollar"></i>
                        <input type="number" step="0.01" min="0" name="precio" class="form-control" required value="<?= htmlspecialchars($producto['precio']) ?>">
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Stock</label>
                        <i class="bi bi-box-seam"></i>
                        <input type="number" min="0" name="stock" class="form-control" required value="<?= htmlspecialchars($producto['stock']) ?>">
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Estado</label>
                        <i class="bi bi-toggle-on"></i>
                        <select name="estado" class="form-select" required>
                            <option value="activo" <?= $producto['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $producto['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12 input-icon">
                        <label class="form-label visually-hidden">Descripción</label>
                        <i class="bi bi-card-text"></i>
                        <textarea name="descripcion" class="form-control" rows="5" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Imagenes actuales</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <?php foreach ($imagenesProducto as $imagenActual): ?>
                                <?php $esPlaceholder = strpos($imagenActual, 'https://placehold.co/') === 0; ?>
                                <div class="producto-thumb-item">
                                    <img src="<?= htmlspecialchars($imagenActual) ?>" alt="Imagen del producto" class="img-fluid rounded-3 producto-thumb-admin">
                                    <?php if (!$esPlaceholder): ?>
                                        <button type="button" class="producto-thumb-delete" data-delete-image="<?= htmlspecialchars($imagenActual, ENT_QUOTES, 'UTF-8') ?>" aria-label="Quitar imagen">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <label class="form-label">Agregar imagenes</label>
                        <div class="input-icon">
                            <i class="bi bi-image"></i>
                            <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
                        </div>
                        <div class="form-text">Puedes seleccionar varias imagenes nuevas. La primera nueva sera la portada.</div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-farmacia" type="submit"><i class="bi bi-save me-1"></i>Actualizar producto</button>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        const form = document.getElementById('productoForm');
        if (!form) return;

        document.querySelectorAll('[data-delete-image]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!confirm('Quitar esta imagen del producto?')) return;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar_imagenes[]';
                input.value = button.dataset.deleteImage;
                form.appendChild(input);
                const item = button.closest('.producto-thumb-item');
                if (item) item.remove();
            });
        });
    })();
</script>
<?php include 'footer.php';
