<?php
if (!defined('SMTP_USER') && file_exists(__DIR__ . '/config_correo.php')) {
    require_once __DIR__ . '/config_correo.php';
}
$footerEmail = defined('SMTP_USER') ? SMTP_USER : 'admin@farmacia.com';
?>
    </main>
    <footer class="site-footer mt-5">
        <div class="container py-5">
            <div class="row gy-4">
                <div class="col-md-3">
                    <div class="footer-brand text-white">Farmacia SaludPlus</div>
                    <p class="text-muted small mt-3">Cuidando tu salud con productos confiables, servicio rápido y soporte profesional.</p>
                </div>
                <div class="col-md-3">
                    <h5 class="text-white mb-3">Navegación</h5>
                    <ul class="footer-list list-unstyled">
                        <li><a href="index.php"><i class="bi bi-chevron-right"></i> Inicio</a></li>
                        <li><a href="categorias.php"><i class="bi bi-chevron-right"></i> Categorías</a></li>
                        <li><a href="marcas.php"><i class="bi bi-chevron-right"></i> Marcas</a></li>
                        <li><a href="contacto.php"><i class="bi bi-chevron-right"></i> Contacto</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5 class="text-white mb-3">Atención al cliente</h5>
                    <p class="text-muted small mb-2"><i class="bi bi-telephone-fill me-2"></i>+51 900 000 000</p>
                    <p class="text-muted small mb-2"><i class="bi bi-envelope-fill me-2"></i><?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Av. Salud 123, Lima</p>
                </div>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <div class="col-md-3">
                        <h5 class="text-white mb-3">Redes sociales</h5>
                        <div class="d-flex gap-3 mb-3">
                            <a href="#" class="text-white social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="text-white social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="text-white social-icon"><i class="bi bi-whatsapp"></i></a>
                            <a href="mailto:<?= htmlspecialchars($footerEmail, ENT_QUOTES, 'UTF-8') ?>" class="text-white social-icon"><i class="bi bi-envelope"></i></a>
                        </div>
                        <a href="contacto.php" class="btn btn-outline-light btn-sm">Enviar consulta</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="row mt-4">
                <div class="col-md-6 text-muted small">© <?= date('Y') ?> Farmacia SaludPlus. Todos los derechos reservados.</div>
                <div class="col-md-6 text-md-end text-center small text-muted">Diseño profesional y atención especializada.</div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-" crossorigin="anonymous"></script>
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true"></div>
    <script src="js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (el) { new bootstrap.Tooltip(el); });
            if (typeof initDarkMode === 'function') {
                initDarkMode();
            }
            if (typeof processUrlToast === 'function') {
                processUrlToast();
            }
        });
    </script>
</body>
</html>
