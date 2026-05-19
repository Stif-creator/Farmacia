<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$estadoFiltro = $_GET['estado'] ?? 'todos';
if (!in_array($estadoFiltro, ['todos', 'activo', 'inactivo'], true)) {
    $estadoFiltro = 'todos';
}

if ($estadoFiltro === 'todos') {
    $proveedores = $conexion->query('SELECT * FROM proveedores ORDER BY nombre ASC')->fetch_all(MYSQLI_ASSOC);
} else {
    $stmt = $conexion->prepare('SELECT * FROM proveedores WHERE estado = ? ORDER BY nombre ASC');
    $stmt->bind_param('s', $estadoFiltro);
    $stmt->execute();
    $proveedores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include 'header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm mb-4">
            <h2 class="mb-3">Proveedores</h2>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="proveedores.php?estado=todos" class="badge <?= $estadoFiltro === 'todos' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Todos</a>
                <a href="proveedores.php?estado=activo" class="badge <?= $estadoFiltro === 'activo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Activos</a>
                <a href="proveedores.php?estado=inactivo" class="badge <?= $estadoFiltro === 'inactivo' ? 'bg-primary text-white' : 'bg-light text-dark' ?>">Inactivos</a>
            </div>
            <div class="mb-3">
                <input id="proveedoresSearch" data-search-table=".table-responsive table" type="text" class="form-control" placeholder="Buscar proveedores por nombre, correo o telefono">
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Telefono</th>
                            <th>Correo</th>
                            <th>Direccion</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor):
                            $estadoProveedor = $proveedor['estado'] ?? 'activo';
                            $estaActivo = $estadoProveedor === 'activo';
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                                <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                                <td><?= htmlspecialchars($proveedor['correo']) ?></td>
                                <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                                <td><span class="badge <?= $estaActivo ? 'badge-activo' : 'badge-inactivo' ?>"><?= $estaActivo ? 'Activo' : 'Inactivo' ?></span></td>
                                <td class="text-end tabla-acciones">
                                    <a href="editar_proveedor.php?id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <?php if ($estaActivo): ?>
                                        <a href="eliminar_proveedor.php?id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Desactivar proveedor?');"><i class="bi bi-toggle-off me-1"></i>Desactivar</a>
                                    <?php else: ?>
                                        <a href="activar_proveedor.php?id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-sm btn-success"><i class="bi bi-toggle-on me-1"></i>Activar</a>
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
            <h4 class="mb-3">Nuevo proveedor</h4>
            <form action="guardar_proveedor.php" method="post">
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nombre</label>
                    <i class="bi bi-people-fill"></i>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Telefono</label>
                    <i class="bi bi-telephone-fill"></i>
                    <input type="text" name="telefono" class="form-control" placeholder="Telefono" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Correo</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Direccion</label>
                    <i class="bi bi-geo-alt-fill"></i>
                    <textarea name="direccion" class="form-control" rows="3" placeholder="Direccion" required></textarea>
                </div>
                <button class="btn btn-farmacia w-100" type="submit"><i class="bi bi-save me-1"></i>Guardar proveedor</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
