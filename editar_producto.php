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

$categorias = $conexion->query('SELECT * FROM categorias ORDER BY nombre_categoria ASC')->fetch_all(MYSQLI_ASSOC);
$marcas = $conexion->query('SELECT * FROM marcas ORDER BY nombre_marca ASC')->fetch_all(MYSQLI_ASSOC);
$proveedores = $conexion->query('SELECT * FROM proveedores ORDER BY nombre ASC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm auth-card">
            <h3 class="mb-3">Editar producto</h3>
            <form action="actualizar_producto.php" method="post" enctype="multipart/form-data">
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
                                <option value="<?= $categoria['id_categoria'] ?>" <?= $categoria['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($categoria['nombre_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Marca</label>
                        <i class="bi bi-award-fill"></i>
                        <select name="marca" class="form-select" required>
                            <option value="">Selecciona una marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?= $marca['id_marca'] ?>" <?= $marca['id_marca'] == $producto['id_marca'] ? 'selected' : '' ?>><?= htmlspecialchars($marca['nombre_marca']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 input-icon">
                        <label class="form-label visually-hidden">Proveedor</label>
                        <i class="bi bi-people-fill"></i>
                        <select name="proveedor" class="form-select" required>
                            <option value="">Selecciona un proveedor</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?= $proveedor['id_proveedor'] ?>" <?= $proveedor['id_proveedor'] == $producto['id_proveedor'] ? 'selected' : '' ?>><?= htmlspecialchars($proveedor['nombre']) ?></option>
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
                        <label class="form-label">Imagen actual</label>
                        <div class="mb-3">
                            <img src="<?= htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia') ?>" alt="Imagen" class="img-fluid rounded-3" style="max-height:200px;">
                        </div>
                        <label class="form-label">Cambiar imagen</label>
                        <div class="input-icon">
                            <i class="bi bi-image"></i>
                            <input type="file" name="imagen" class="form-control">
                        </div>
                        <div class="form-text">Dejar en blanco para mantener la imagen actual.</div>
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
<?php include 'footer.php';
