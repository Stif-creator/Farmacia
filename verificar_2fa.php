<?php
require_once 'auth.php';
require_once 'conexion.php';

if (!isset($_SESSION['pendiente_2fa']) || $_SESSION['rol_pendiente'] === 'admin') {
    header('Location: login.php');
    exit;
}

$errores = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigoIngresado = trim($_POST['codigo'] ?? '');
    if (empty($codigoIngresado)) {
        $errores[] = 'Ingresa el código de verificación.';
    } elseif (!isset($_SESSION['codigo_2fa_expira']) || time() > $_SESSION['codigo_2fa_expira']) {
        $errores[] = 'El código ha expirado. Solicita uno nuevo.';
    } elseif ($codigoIngresado === $_SESSION['codigo_2fa_temp']) {
        $_SESSION['id_usuario'] = $_SESSION['id_pendiente'];
        $_SESSION['usuario'] = $_SESSION['nombre_pendiente'];
        $_SESSION['correo'] = $_SESSION['correo_pendiente'];
        $_SESSION['rol'] = $_SESSION['rol_pendiente'];

        $update = $conexion->prepare('UPDATE usuarios SET codigo_2fa = NULL WHERE id_usuario = ?');
        $update->bind_param('i', $_SESSION['id_pendiente']);
        $update->execute();

        unset($_SESSION['pendiente_2fa']);
        unset($_SESSION['id_pendiente']);
        unset($_SESSION['correo_pendiente']);
        unset($_SESSION['nombre_pendiente']);
        unset($_SESSION['rol_pendiente']);
        unset($_SESSION['codigo_2fa_temp']);
        unset($_SESSION['codigo_2fa_expira']);

        header('Location: index.php');
        exit;
    } else {
        $errores[] = 'Código de verificación incorrecto.';
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm p-4 auth-card text-center">
            <div class="icon-auth mb-2"><i class="bi bi-key-fill"></i></div>
            <h2 class="mb-3">Verificar código 2FA</h2>
            <p class="text-secondary">Revisa tu correo electrónico, te enviamos un código de verificación.</p>
            <?php if ($mensaje): ?>
                <div class="alert alert-success alert-message"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($errores): ?>
                <div class="alert alert-danger alert-message">
                    <?php foreach ($errores as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="post" action="verificar_2fa.php" novalidate>
                <div class="mb-4 input-icon">
                    <label for="codigo" class="form-label visually-hidden">Código de 6 dígitos</label>
                    <i class="bi bi-shield-lock"></i>
                    <input type="text" class="form-control text-center" id="codigo" name="codigo" maxlength="6" placeholder="000000" required>
                </div>
                <button class="btn btn-farmacia w-100" type="submit">Verificar</button>
            </form>
            <div class="text-center mt-3">
                <a href="reenviar_2fa.php" class="btn btn-outline-farmacia btn-sm">Reenviar código</a>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php';
