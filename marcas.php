<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$marcas = $conexion->query('SELECT * FROM marcas ORDER BY nombre_marca ASC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm mb-4">
            <h2 class="mb-3">Marcas</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Descripción</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marcas as $marca): ?>
                            <tr>
                                <td><?= htmlspecialchars($marca['nombre_marca']) ?></td>
                                <td><?= htmlspecialchars($marca['descripcion']) ?></td>
                                <td class="text-end tabla-acciones">
                                    <a href="editar_marca.php?id=<?= $marca['id_marca'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <a href="eliminar_marca.php?id=<?= $marca['id_marca'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar marca?');"><i class="bi bi-trash me-1"></i>Eliminar</a>
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
            <h4 class="mb-3">Nueva marca</h4>
            <form action="guardar_marca.php" method="post">
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nombre de marca</label>
                    <i class="bi bi-award-fill"></i>
                    <input type="text" name="nombre_marca" class="form-control" placeholder="Nombre de marca" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Descripción</label>
                    <i class="bi bi-card-text"></i>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción breve" required></textarea>
                </div>
                <button class="btn btn-farmacia w-100" type="submit"><i class="bi bi-save me-1"></i>Guardar marca</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';