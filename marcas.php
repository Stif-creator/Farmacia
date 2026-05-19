<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$estadoFiltro = $_GET['estado'] ?? 'todos';
if (!in_array($estadoFiltro, ['todos', 'activo', 'inactivo'], true)) {
    $estadoFiltro = 'todos';
}

if ($estadoFiltro === 'todos') {
    $marcas = $conexion->query('SELECT * FROM marcas ORDER BY nombre_marca ASC')->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conexion->prepare('SELECT * FROM marcas WHERE estado = ? ORDER BY nombre_marca ASC');
    $stmt->bind_param('s', $estadoFiltro);
    $stmt->execute();
    $marcas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include 'header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm mb-4">
            <h2 class="mb-3">Marcas</h2>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="marcas.php?estado=todos" class="badge <?= $estadoFiltro === 'todos' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Todos</a>
                <a href="marcas.php?estado=activo" class="badge <?= $estadoFiltro === 'activo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Activas</a>
                <a href="marcas.php?estado=inactivo" class="badge <?= $estadoFiltro === 'inactivo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Inactivas</a>
            </div>
            <div class="mb-3">
                <input id="marcasSearch" data-search-table=".table-responsive table" type="text" class="form-control" placeholder="Buscar marcas por nombre o descripcion">
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Descripcion</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marcas as $marca):
                            $estadoMarca = $marca['estado'] ?? 'activo';
                            $estaActiva = $estadoMarca === 'activo';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($marca['nombre_marca']) ?></td>
                                <td><?= htmlspecialchars($marca['descripcion']) ?></td>
                                <td><span class="badge <?= $estaActiva ? 'badge-activo' : 'badge-inactivo' ?>"><?= $estaActiva ? 'Activo' : 'Inactivo' ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="editar_marca.php?id=<?= $marca['id_marca'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <?php if ($estaActiva): ?>
                                        <a href="eliminar_marca.php?id=<?= $marca['id_marca'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desactivar marca?');"><i class="bi bi-toggle-off me-1"></i>Desactivar</a>
                                    <?php else: ?>
                                        <a href="activar_marca.php?id=<?= $marca['id_marca'] ?>" class="btn btn-sm btn-success"><i class="bi bi-toggle-on me-1"></i>Activar</a>
                                    <?php endif; ?>
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
                    <label class="form-label visually-hidden">Descripcion</label>
                    <i class="bi bi-card-text"></i>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripcion breve" required></textarea>
                </div>
                <button class="btn btn-farmacia w-100" type="submit"><i class="bi bi-save me-1"></i>Guardar marca</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
