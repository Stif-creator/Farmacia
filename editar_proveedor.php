<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: proveedores.php');
    exit;
}

$query = $conexion->prepare('SELECT * FROM proveedores WHERE id_proveedor = ? LIMIT 1');
$query->bind_param('i', $id);
$query->execute();
$resultado = $query->get_result();
$proveedor = $resultado->fetch_assoc();

if (!$proveedor) {
    header('Location: proveedores.php');
    exit;
}
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm">
            <h2 class="mb-3">Editar proveedor</h2>
            <form action="actualizar_proveedor.php" method="post">
                <input type="hidden" name="id_proveedor" value="<?= $proveedor['id_proveedor'] ?>">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($proveedor['nombre']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required value="<?= htmlspecialchars($proveedor['telefono']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($proveedor['correo']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="3" required><?= htmlspecialchars($proveedor['direccion']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-farmacia" type="submit">Actualizar proveedor</button>
                    <a href="proveedores.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';