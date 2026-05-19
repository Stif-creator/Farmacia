<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$usuarios = $conexion->query('SELECT u.*, COUNT(v.id_venta) AS ventas FROM usuarios u LEFT JOIN ventas v ON u.id_usuario = v.id_usuario GROUP BY u.id_usuario ORDER BY u.rol DESC, u.nombre ASC')->fetch_all(MYSQLI_ASSOC);

$existeAdminHardcoded = false;
foreach ($usuarios as $usuarioFila) {
    if ($usuarioFila['correo'] === 'admin@farmacia.com') {
        $existeAdminHardcoded = true;
        break;
    }
}

if (! $existeAdminHardcoded) {
    array_unshift($usuarios, [
        'id_usuario' => 'hardcoded_admin',
        'nombre' => obtenerNombreAdminHardcodeado(),
        'correo' => HARDCODED_ADMIN_EMAIL,
        'rol' => 'admin',
        'estado' => 'activo',
        'ventas' => 0,
    ]);
}

$totalUsuarios = count($usuarios);
$totalAdmins = 0;
$totalBloqueados = 0;
foreach ($usuarios as $usuarioFila) {
    if ($usuarioFila['rol'] === 'admin') {
        $totalAdmins++;
    }
    if ($usuarioFila['estado'] === 'bloqueado') {
        $totalBloqueados++;
    }
}

include 'header.php';
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-10">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="section-titulo">
                    <h2>Gestión de usuarios</h2>
                    <p class="text-secondary">Revisa, edita y controla cuentas de clientes y administradores desde un panel central.</p>
                </div>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <span class="badge bg-primary">Total usuarios: <?= $totalUsuarios ?></span>
                        <span class="badge bg-danger">Admins: <?= $totalAdmins ?></span>
                        <span class="badge bg-warning text-dark">Bloqueados: <?= $totalBloqueados ?></span>
                    </div>
            </div>
                <div class="d-flex gap-2">
                    <a href="crear_admin.php" class="btn btn-gradient btn-lg shadow-sm"><i class="bi bi-person-plus me-2"></i>Crear Administrador</a>
                    <a href="crear_usuario.php" class="btn btn-outline-primary btn-lg shadow-sm"><i class="bi bi-person-plus-fill me-2"></i>Crear Usuario</a>
                </div>
        </div>
        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success alert-message"><?= htmlspecialchars($_GET['mensaje']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-message"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nombre / Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Compras</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($usuario['nombre']) ?>
                                <div class="small text-muted"><?= htmlspecialchars($usuario['correo']) ?></div>
                            </td>
                            <td>
                                <?php if ($usuario['rol'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Cliente</span>
                                <?php endif; ?>
                                <?php if ($usuario['correo'] === HARDCODED_ADMIN_EMAIL): ?>
                                    <span class="badge bg-purple">Admin Principal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usuario['correo'] === HARDCODED_ADMIN_EMAIL): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php elseif ($usuario['estado'] === 'bloqueado'): ?>
                                    <span class="badge bg-danger">Bloqueado</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php endif; ?>
                            </td>
                            <td><?= intval($usuario['ventas']) ?></td>
                            <td class="text-end tabla-acciones">
                                <?php $esHardcode = $usuario['correo'] === HARDCODED_ADMIN_EMAIL; ?>
                                <?php $idUrl = $esHardcode ? 'hardcoded_admin' : $usuario['id_usuario']; ?>
                                <?php if (! $esHardcode): ?>
                                    <a href="editar_usuario.php?id=<?= $idUrl ?>" class="btn btn-sm btn-secondary"><i class="bi bi-pencil-square"></i></a>
                                    <?php if ($usuario['estado'] === 'activo' && $usuario['id_usuario'] !== $_SESSION['id_usuario']): ?>
                                        <a href="actualizar_usuario.php?id=<?= $idUrl ?>&accion=bloquear" class="btn btn-sm btn-warning" onclick="return confirm('Bloquear este usuario?');"><i class="bi bi-unlock-fill text-success"></i></a>
                                    <?php elseif ($usuario['estado'] === 'bloqueado'): ?>
                                        <a href="actualizar_usuario.php?id=<?= $idUrl ?>&accion=desbloquear" class="btn btn-sm btn-success" onclick="return confirm('Desbloquear este usuario?');"><i class="bi bi-lock-fill text-warning"></i></a>
                                    <?php endif; ?>
                                    <?php if ($usuario['id_usuario'] !== $_SESSION['id_usuario'] && ! $esHardcode): ?>
                                        <a href="actualizar_usuario.php?id=<?= $idUrl ?>&accion=eliminar" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar este usuario?');"><i class="bi bi-trash-fill"></i></a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-secondary">Protegido</span>
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
