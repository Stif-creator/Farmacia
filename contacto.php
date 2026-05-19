<?php
require_once 'auth.php';
protegerRuta();
include 'header.php';

$mensajeSuccess = $_SESSION['contacto_exito'] ?? '';
$mensajeError = $_SESSION['contacto_error'] ?? '';
unset($_SESSION['contacto_exito'], $_SESSION['contacto_error']);
?>
<div class="row justify-content-center mb-5">
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm auth-card">
            <div class="d-flex align-items-center mb-3 gap-3">
                <div class="icon-auth"><i class="bi bi-chat-dots-fill"></i></div>
                <div>
                    <h3 class="mb-1">Contacto y soporte</h3>
                    <p class="text-secondary mb-0">Envía sugerencias, reportes o consultas directamente al equipo de farmacia.</p>
                </div>
            </div>
            <?php if ($mensajeSuccess): ?>
                <div class="alert alert-success alert-message"><?= htmlspecialchars($mensajeSuccess) ?></div>
            <?php endif; ?>
            <?php if ($mensajeError): ?>
                <div class="alert alert-danger alert-message"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>
            <div class="mb-4 p-3 rounded-4" style="background: rgba(15,157,88,0.08);">
                <div class="fw-semibold mb-2">Contacto registrado como:</div>
                <div><i class="bi bi-person-fill me-2"></i> <?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario') ?></div>
                <div><i class="bi bi-envelope-fill me-2"></i> <?= htmlspecialchars($_SESSION['correo'] ?? '') ?></div>
            </div>
            <form action="enviar_contacto.php" method="post" novalidate>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Asunto</label>
                    <i class="bi bi-tag-fill"></i>
                    <input type="text" name="asunto" class="form-control" placeholder="Asunto" required>
                </div>
                <div class="mb-3 input-icon">
                    <label class="form-label visually-hidden">Categoría</label>
                    <i class="bi bi-list-ul"></i>
                    <select name="categoria" class="form-select" required>
                        <option value="">Selecciona categoría</option>
                        <option value="Soporte">Soporte</option>
                        <option value="Sugerencia">Sugerencia</option>
                        <option value="Reporte">Reporte</option>
                        <option value="Consulta">Consulta</option>
                    </select>
                </div>
                <div class="mb-4 input-icon">
                    <label class="form-label visually-hidden">Mensaje</label>
                    <i class="bi bi-chat-square-text"></i>
                    <textarea name="mensaje" class="form-control" rows="6" placeholder="Escribe tu mensaje aquí" required></textarea>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-farmacia"><i class="bi bi-send-fill me-1"></i>Enviar mensaje</button>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i>Volver a la tienda</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'footer.php';
