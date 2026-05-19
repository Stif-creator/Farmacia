<?php
require_once 'auth.php';
include 'header.php';
$mensaje = $_SESSION['desbloqueo_exito'] ?? '';
$error = $_SESSION['desbloqueo_error'] ?? '';
unset($_SESSION['desbloqueo_exito'], $_SESSION['desbloqueo_error']);
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm auth-card">
            <div class="d-flex align-items-center mb-3 gap-3">
                <div class="icon-auth"><i class="bi bi-unlock-fill"></i></div>
                <div>
                    <h3 class="mb-1">Solicitar desbloqueo de cuenta</h3>
                    <p class="text-secondary mb-0">Si tu cuenta está bloqueada, envía una solicitud y el administrador la revisará.</p>
                </div>
            </div>
            <?php if ($mensaje): ?>
                <div class="alert alert-success alert-message"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="enviar_desbloqueo.php" method="post" novalidate>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Correo</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" name="correo" class="form-control" placeholder="tu@correo.com" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Asunto</label>
                    <i class="bi bi-tag-fill"></i>
                    <input type="text" name="asunto" class="form-control" value="Solicitud de desbloqueo de cuenta" required>
                </div>
                <div class="mb-4 input-icon">
                    <label class="form-label visually-hidden">Mensaje</label>
                    <i class="bi bi-chat-square-text"></i>
                    <textarea name="mensaje" class="form-control" rows="6" placeholder="Explica por qué necesitas el desbloqueo" required></textarea>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-farmacia"><i class="bi bi-send-fill me-1"></i>Enviar solicitud</button>
                    <a href="login.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i>Volver al login</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
