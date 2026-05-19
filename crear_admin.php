<?php
require_once 'auth.php';
soloAdmin();
include 'header.php';
$mensaje = $_GET['mensaje'] ?? '';
$error = $_GET['error'] ?? '';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm auth-card">
            <div class="d-flex align-items-center mb-3 gap-3">
                <div class="icon-auth"><i class="bi bi-person-plus-fill"></i></div>
                <div>
                    <h3 class="mb-1">Crear Administrador</h3>
                    <p class="text-secondary mb-0">Agrega un nuevo administrador con control seguro y acceso completo.</p>
                </div>
            </div>
            <?php if ($mensaje): ?>
                <div class="alert alert-success alert-message"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="guardar_admin.php" method="post" novalidate>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Nombre</label>
                    <i class="bi bi-person-fill"></i>
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre completo" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Correo</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="correo" class="form-control" placeholder="admin@farmacia.com" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required>
                </div>
                <div class="mb-4 input-icon">
                    <label class="form-label visually-hidden">Confirmar contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" name="confirmar" class="form-control" placeholder="Confirmar contraseña" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-farmacia"><i class="bi bi-save me-1"></i>Guardar administrador</button>
                    <a href="admin_usuarios.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i>Volver al panel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
