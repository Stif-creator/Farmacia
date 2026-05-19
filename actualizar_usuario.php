<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = $_REQUEST['id'] ?? '';
$esHardcode = $id === 'hardcoded_admin';

$accion = $_GET['accion'] ?? '';
if ($accion !== '') {
    if ($esHardcode) {
        header('Location: admin_usuarios.php?error=' . urlencode('El admin hardcodeado no puede realizar esta acción.'));
        exit;
    }

    $idUsuario = intval($id);
    if ($idUsuario <= 0) {
        header('Location: admin_usuarios.php?error=' . urlencode('ID de usuario inválido.'));
        exit;
    }

    $consulta = $conexion->prepare('SELECT id_usuario, correo FROM usuarios WHERE id_usuario = ? LIMIT 1');
    $consulta->bind_param('i', $idUsuario);
    $consulta->execute();
    $usuario = $consulta->get_result()->fetch_assoc();
    if (! $usuario || $usuario['correo'] === HARDCODED_ADMIN_EMAIL) {
        header('Location: admin_usuarios.php?error=' . urlencode('Acción no válida para este usuario.'));
        exit;
    }
    if ($accion === 'bloquear' || $accion === 'desbloquear') {
        if ($idUsuario === $_SESSION['id_usuario']) {
            header('Location: admin_usuarios.php?error=' . urlencode('No puedes cambiar el estado de tu propia cuenta aquí.'));
            exit;
        }
        $nuevoEstado = $accion === 'bloquear' ? 'bloqueado' : 'activo';
        $update = $conexion->prepare('UPDATE usuarios SET estado = ? WHERE id_usuario = ?');
        $update->bind_param('si', $nuevoEstado, $idUsuario);
        $update->execute();
        $mensaje = $accion === 'bloquear' ? 'Usuario bloqueado correctamente.' : 'Usuario desbloqueado correctamente.';
        header('Location: admin_usuarios.php?mensaje=' . urlencode($mensaje));
        exit;
    }
    if ($accion === 'eliminar') {
        if ($idUsuario === $_SESSION['id_usuario']) {
            header('Location: admin_usuarios.php?error=' . urlencode('No puedes eliminar tu propia cuenta.'));
            exit;
        }
        $delete = $conexion->prepare('DELETE FROM usuarios WHERE id_usuario = ?');
        $delete->bind_param('i', $idUsuario);
        $delete->execute();
        header('Location: admin_usuarios.php?mensaje=' . urlencode('Usuario eliminado correctamente.'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_POST['id_usuario'] ?? '';
    $nombre = limpiarTexto($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $rol = $_POST['rol'] === 'admin' ? 'admin' : 'cliente';
    $estado = $_POST['estado'] === 'bloqueado' ? 'bloqueado' : 'activo';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($nombre === '' || $correo === '') {
        header('Location: editar_usuario.php?id=' . urlencode($idUsuario) . '&error=' . urlencode('Datos incompletos.'));
        exit;
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header('Location: editar_usuario.php?id=' . urlencode($idUsuario) . '&error=' . urlencode('Correo inválido.'));
        exit;
    }

    if ($idUsuario === 'hardcoded_admin') {
        if ($contrasena !== '' && $contrasena !== $confirmar) {
            header('Location: editar_usuario.php?id=hardcoded_admin&error=' . urlencode('Las contraseñas no coinciden.'));
            exit;
        }
        $hash = null;
        if ($contrasena !== '') {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        }
        guardarAdminHardcodeado($nombre, $hash);
        if (isset($_SESSION['correo']) && $_SESSION['correo'] === HARDCODED_ADMIN_EMAIL) {
            $_SESSION['usuario'] = $nombre;
        }
        header('Location: admin_usuarios.php?mensaje=' . urlencode('Administrador hardcodeado actualizado correctamente.'));
        exit;
    }

    if ($idUsuario === HARDCODED_ADMIN_EMAIL || esAdminHardcodeado($idUsuario)) {
        header('Location: admin_usuarios.php?error=' . urlencode('Acción no válida para este usuario.'));
        exit;
    }

    $idUsuarioInt = intval($idUsuario);
    if ($idUsuarioInt <= 0) {
        header('Location: admin_usuarios.php?error=' . urlencode('ID de usuario inválido.'));
        exit;
    }

    $queryUnico = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario <> ? LIMIT 1');
    $queryUnico->bind_param('si', $correo, $idUsuarioInt);
    $queryUnico->execute();
    if ($queryUnico->get_result()->fetch_assoc()) {
        header('Location: editar_usuario.php?id=' . urlencode($idUsuario) . '&error=' . urlencode('El correo ya está en uso.'));
        exit;
    }

    $sql = 'UPDATE usuarios SET nombre = ?, correo = ?, rol = ?, estado = ?';
    $params = [$nombre, $correo, $rol, $estado, $idUsuarioInt];
    $types = 'ssssi';
    if ($contrasena !== '') {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql .= ', contrasena = ?';
        array_splice($params, 4, 0, [$hash]);
        $types = 'sssssi';
    }
    $sql .= ' WHERE id_usuario = ?';

    $stmt = $conexion->prepare($sql);
    if ($contrasena !== '') {
        $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
    } else {
        $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4]);
    }
    $stmt->execute();

    if ($idUsuarioInt === $_SESSION['id_usuario']) {
        $_SESSION['usuario'] = $nombre;
        $_SESSION['correo'] = $correo;
    }

    header('Location: admin_usuarios.php?mensaje=' . urlencode('Usuario actualizado correctamente.'));
    exit;
}

header('Location: admin_usuarios.php?error=' . urlencode('No se recibió ninguna acción válida.'));
exit;
