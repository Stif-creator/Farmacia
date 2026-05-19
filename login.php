<?php
require_once 'auth.php';
require_once 'conexion.php';
require_once 'enviar_correo.php';

$errores = [];
$mensajeLoginRequerido = isset($_GET['login_required']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($correo) || empty($contrasena)) {
        $errores[] = 'Debe ingresar correo y contraseña.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    } else {
        $esAdminHardcodeado = esAdminHardcodeado($correo);
        if ($esAdminHardcodeado) {
            if (password_verify($contrasena, obtenerPasswordAdminHardcodeado())) {
                $_SESSION['id_usuario'] = 'hardcoded_admin';
                $_SESSION['usuario'] = obtenerNombreAdminHardcodeado();
                $_SESSION['correo'] = HARDCODED_ADMIN_EMAIL;
                $_SESSION['rol'] = 'admin';
                header('Location: dashboard_admin.php');
                exit;
            }
            $errores[] = 'Credenciales incorrectas.';
        } else {
            // Buscar en base de datos y enviar 2FA
            $query = $conexion->prepare('SELECT id_usuario, nombre, correo, contrasena, rol, estado FROM usuarios WHERE correo = ? LIMIT 1');
            $query->bind_param('s', $correo);
            $query->execute();
            $resultado = $query->get_result();
            $usuario = $resultado->fetch_assoc();

            if ($usuario && $usuario['estado'] === 'bloqueado') {
                $mensajeBloqueado = true;
                $errores[] = 'Tu cuenta ha sido bloqueada.';
            } elseif ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                $codigo = strval(rand(100000, 999999));

                $_SESSION['pendiente_2fa'] = true;
                $_SESSION['id_pendiente'] = $usuario['id_usuario'];
                $_SESSION['correo_pendiente'] = $usuario['correo'];
                $_SESSION['nombre_pendiente'] = $usuario['nombre'];
                $_SESSION['rol_pendiente'] = $usuario['rol'];
                $_SESSION['codigo_2fa_temp'] = $codigo;
                $_SESSION['codigo_2fa_expira'] = time() + 300;

                // (Opcional) actualizar la columna en la tabla usuarios para referencia
                $update = $conexion->prepare('UPDATE usuarios SET codigo_2fa = ? WHERE id_usuario = ?');
                $update->bind_param('si', $codigo, $usuario['id_usuario']);
                $update->execute();

                // Enviar exactamente el código generado
                if (!enviarCodigo2FA($usuario['correo'], $usuario['nombre'], $codigo)) {
                    $errores[] = 'No se pudo enviar el código. Revisa la configuración SMTP.';
                } else {
                    header('Location: verificar_2fa.php');
                    exit;
                }
            } else {
                $errores[] = 'Credenciales incorrectas.';
            }
        }
    }
}

include 'header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm p-4 auth-card text-center">
            <div class="icon-auth mb-2"><i class="bi bi-shield-lock-fill"></i></div>
            <h2 class="mb-3">Iniciar sesión</h2>
            <p class="text-secondary">Ingresa tus datos para recibir un código de acceso por correo.</p>
            <?php if ($errores): ?>
                <div class="alert alert-danger alert-message">
                    <?php foreach ($errores as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <?php if (!empty($mensajeBloqueado)): ?>
                        <div class="mt-2"> 
                            <a href="solicitar_desbloqueo.php" class="link-primary" style="text-decoration:underline; color:#3da9ff;">Solicitar desbloqueo</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($mensajeLoginRequerido): ?>
                <div class="alert alert-warning alert-message">Debes iniciar sesión para comprar productos.</div>
            <?php endif; ?>
            <form method="post" action="login.php" novalidate>
                <div class="mb-3 input-icon">
                    <label for="correo" class="form-label visually-hidden">Correo electrónico</label>
                    <i class="bi bi-envelope-fill"></i>
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="tu@correo.com" required value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                </div>
                <div class="mb-4 input-icon">
                    <label for="contrasena" class="form-label visually-hidden">Contraseña</label>
                    <i class="bi bi-lock-fill"></i>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña" required>
                </div>
                <button class="btn btn-farmacia w-100" type="submit">Enviar código 2FA</button>
            </form>
            <p class="mt-3 text-center">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
        </div>
    </div>
</div>
<?php include 'footer.php';
