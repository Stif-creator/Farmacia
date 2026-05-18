<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: categorias.php');
    exit;
}

$query = $conexion->prepare('SELECT * FROM categorias WHERE id_categoria = ? LIMIT 1');
$query->bind_param('i', $id);
$query->execute();
$resultado = $query->get_result();
$categoria = $resultado->fetch_assoc();

if (!$categoria) {
    header('Location: categorias.php');
    exit;
}
include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm">
            <h2 class="mb-3">Editar categoría</h2>
            <form action="actualizar_categoria.php" method="post">
                <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre_categoria" class="form-control" required value="<?= htmlspecialchars($categoria['nombre_categoria']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-farmacia" type="submit">Actualizar categoría</button>
                    <a href="categorias.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
