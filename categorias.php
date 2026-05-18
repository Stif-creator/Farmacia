<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$categorias = $conexion->query('SELECT * FROM categorias ORDER BY nombre_categoria ASC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm mb-4">
            <h2 class="mb-3">Categorías</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <tr>
                                <td><?= htmlspecialchars($categoria['nombre_categoria']) ?></td>
                                <td><?= htmlspecialchars($categoria['descripcion']) ?></td>
                                <td class="text-end tabla-acciones">
                                    <a href="editar_categoria.php?id=<?= $categoria['id_categoria'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <a href="eliminar_categoria.php?id=<?= $categoria['id_categoria'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar categoría?');"><i class="bi bi-trash me-1"></i>Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm auth-card">
            <h4 class="mb-3">Nueva categoría</h4>
            <form action="guardar_categoria.php" method="post">
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nombre</label>
                    <i class="bi bi-tags-fill"></i>
                    <input type="text" name="nombre_categoria" class="form-control" placeholder="Nombre" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Descripción</label>
                    <i class="bi bi-card-text"></i>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción" required></textarea>
                </div>
                <button class="btn btn-farmacia w-100" type="submit"><i class="bi bi-save me-1"></i>Guardar categoría</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
