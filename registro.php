<?php
require_once 'auth.php';
require_once 'conexion.php';

$errores = [];
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (empty($nombre) || empty($correo) || empty($contrasena) || empty($confirmar)) {
        $errores[] = 'Todos los campos son obligatorios.';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingrese un correo válido.';
    }
    if (strlen($contrasena) < 8 || !preg_match('/[A-Z]/', $contrasena) || !preg_match('/\d/', $contrasena)) {
        $errores[] = 'La contraseña debe tener mínimo 8 caracteres, una mayúscula y un número.';
    }
    if ($contrasena !== $confirmar) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $check = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1');
        $check->bind_param('s', $correo);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errores[] = 'El correo ya está registrado.';
        } else {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $insert = $conexion->prepare('INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ? )');
            $rol = 'cliente';
            $insert->bind_param('ssss', $nombre, $correo, $hash, $rol);
            $insert->execute();
            $mensaje = 'Registro exitoso. Ya puedes iniciar sesión.';
            $_POST = [];
        }
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm p-4 auth-card text-center">
            <div class="icon-auth mb-2"><i class="bi bi-person-plus-fill"></i></div>
            <h2 class="mb-3">Crear cuenta</h2>
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
            <form method="post" action="registro.php" novalidate>
                <div class="mb-3 input-icon">
                    <label for="nombre" class="form-label visually-hidden">Nombre completo</label>
                    <i class="bi bi-person-fill"></i>
                    <input type="text" class="form-control" id="nombre" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="mb-3 input-icon">
                    <label for="correo" class="form-label visually-hidden">Correo electrónico</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" class="form-control" id="correo" name="correo" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                </div>
                <div class="mb-3 input-icon">
                    <label for="contrasena" class="form-label visually-hidden">Contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>
                <div class="mb-4 input-icon">
                    <label for="confirmar" class="form-label visually-hidden">Confirmar contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" class="form-control" id="confirmar" name="confirmar" required>
                </div>
                <button class="btn btn-farmacia w-100" type="submit">Registrar cuenta</button>
            </form>
            <p class="mt-3 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</div>
<?php include 'footer.php';
