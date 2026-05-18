<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$proveedores = $conexion->query('SELECT * FROM proveedores ORDER BY nombre ASC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm mb-4">
            <h2 class="mb-3">Proveedores</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Dirección</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                                <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                                <td><?= htmlspecialchars($proveedor['correo']) ?></td>
                                <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                                <td class="text-end tabla-acciones">
                                    <a href="editar_proveedor.php?id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil me-1"></i>Editar</a>
                                    <a href="eliminar_proveedor.php?id=<?= $proveedor['id_proveedor'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar proveedor?');"><i class="bi bi-trash me-1"></i>Eliminar</a>
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
                    <label class="form-label visually-hidden">Teléfono</label>
                    <i class="bi bi-telephone-fill"></i>
                    <input type="text" name="telefono" class="form-control" placeholder="Teléfono" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Correo</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="correo" class="form-control" placeholder="correo@ejemplo.com" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Dirección</label>
                    <i class="bi bi-geo-alt-fill"></i>
                    <textarea name="direccion" class="form-control" rows="3" placeholder="Dirección" required></textarea>
                </div>
                <button class="btn btn-farmacia w-100" type="submit"><i class="bi bi-save me-1"></i>Guardar proveedor</button>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';