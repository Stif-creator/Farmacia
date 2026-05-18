<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$usuarios = $conexion->query('SELECT u.*, COUNT(v.id_venta) AS ventas FROM usuarios u LEFT JOIN ventas v ON u.id_usuario = v.id_usuario GROUP BY u.id_usuario ORDER BY u.nombre ASC')->fetch_all(MYSQLI_ASSOC);
include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="section-titulo">
            <h2>Gestión de usuarios</h2>
            <p class="text-secondary">Revisa y elimina usuarios siempre que no tengan ventas.</p>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Compras</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td><?= htmlspecialchars($usuario['rol']) ?></td>
                            <td><?= intval($usuario['ventas']) ?></td>
                            <td class="text-end tabla-acciones">
                                <?php if ($usuario['id_usuario'] !== $_SESSION['id_usuario'] && intval($usuario['ventas']) === 0): ?>
                                    <a href="eliminar_usuario.php?id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar usuario?');">Eliminar</a>
                                <?php else: ?>
                                    <span class="text-secondary">No disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'footer.php';
