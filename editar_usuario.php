<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = $_GET['id'] ?? '';
$esHardcode = $id === 'hardcoded_admin';
if ($esHardcode) {
    $usuario = [
        'id_usuario' => 'hardcoded_admin',
        'nombre' => obtenerNombreAdminHardcodeado(),
        'correo' => HARDCODED_ADMIN_EMAIL,
        'rol' => 'admin',
        'estado' => 'activo',
    ];
} else {
    $id = intval($id);
    if ($id <= 0) {
        header('Location: admin_usuarios.php?error=' . urlencode('Usuario inválido.'));
        exit;
    }

    $query = $conexion->prepare('SELECT * FROM usuarios WHERE id_usuario = ? LIMIT 1');
    $query->bind_param('i', $id);
    $query->execute();
    $usuario = $query->get_result()->fetch_assoc();
    if (! $usuario) {
        header('Location: admin_usuarios.php?error=' . urlencode('Usuario no encontrado.'));
        exit;
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm auth-card">
            <div class="d-flex align-items-center mb-3 gap-3">
                <div class="icon-auth"><i class="bi bi-pencil-square"></i></div>
                <div>
                    <h3 class="mb-1">Editar usuario</h3>
                    <p class="text-secondary mb-0">Actualiza los datos del usuario, su rol y estado de acceso.</p>
                </div>
            </div>
            <form action="actualizar_usuario.php" method="post" novalidate>
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nombre</label>
                    <i class="bi bi-person-fill"></i>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required value="<?= htmlspecialchars($usuario['nombre']) ?>">
                </div>
                <?php if ($esHardcode): ?>
                    <div class="mb-3 input-icon">
                        <label class="form-label visually-hidden">Correo</label>
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" class="form-control" placeholder="Correo electrónico" readonly value="<?= htmlspecialchars($usuario['correo']) ?>">
                        <input type="hidden" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>">
                    </div>
                    <div class="mb-3 input-icon">
                        <label class="form-label visually-hidden">Rol</label>
                        <i class="bi bi-shield-lock-fill"></i>
                        <input type="text" class="form-control" readonly value="Administrador">
                        <input type="hidden" name="rol" value="admin">
                    </div>
                <?php else: ?>
                    <div class="mb-3 input-icon">
                        <label class="form-label visually-hidden">Correo</label>
                        <i class="bi bi-envelope-fill"></i>
                        <input type="email" name="correo" class="form-control" placeholder="Correo electrónico" required value="<?= htmlspecialchars($usuario['correo']) ?>">
                    </div>
                    <div class="mb-3 input-icon">
                        <label class="form-label visually-hidden">Rol</label>
                        <i class="bi bi-shield-lock-fill"></i>
                        <select name="rol" class="form-select" required>
                            <option value="cliente" <?= $usuario['rol'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="mb-3 input-icon">
                        <label class="form-label visually-hidden">Estado</label>
                        <i class="bi bi-activity"></i>
                        <select name="estado" class="form-select" required>
                            <option value="activo" <?= $usuario['estado'] !== 'bloqueado' ? 'selected' : '' ?>>Activo</option>
                            <option value="bloqueado" <?= $usuario['estado'] === 'bloqueado' ? 'selected' : '' ?>>Bloqueado</option>
                        </select>
                    </div>
                <?php endif; ?>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nueva contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="contrasena" class="form-control" placeholder="Nueva contraseña (opcional)">
                </div>
                <div class="mb-4 input-icon">
                    <label class="form-label visually-hidden">Confirmar contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="confirmar" class="form-control" placeholder="Confirmar contraseña">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-farmacia"><i class="bi bi-save me-1"></i>Actualizar usuario</button>
                    <a href="admin_usuarios.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i>Volver</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
