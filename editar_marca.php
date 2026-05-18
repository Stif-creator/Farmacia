<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: marcas.php');
    exit;
}

$query = $conexion->prepare('SELECT * FROM marcas WHERE id_marca = ? LIMIT 1');
$query->bind_param('i', $id);
$query->execute();
$resultado = $query->get_result();
$marca = $resultado->fetch_assoc();

if (!$marca) {
    header('Location: marcas.php');
    exit;
}
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm">
            <h2 class="mb-3">Editar marca</h2>
            <form action="actualizar_marca.php" method="post">
                <input type="hidden" name="id_marca" value="<?= $marca['id_marca'] ?>">
                <div class="mb-3">
                    <label class="form-label">Nombre de marca</label>
                    <input type="text" name="nombre_marca" class="form-control" required value="<?= htmlspecialchars($marca['nombre_marca']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($marca['descripcion']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-farmacia" type="submit">Actualizar marca</button>
                    <a href="marcas.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';