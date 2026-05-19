<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

function redirect_with($url, $params = []) {
    $qs = http_build_query($params);
    header('Location: ' . $url . ($qs ? ('?' . $qs) : ''));
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';
$estado = $_POST['estado'] ?? 'activo';
$rol = $_POST['rol'] ?? 'cliente';

if ($nombre === '' || $correo === '' || $contrasena === '' || $confirmar === '') {
    redirect_with('crear_usuario.php', ['error' => 'Todos los campos son obligatorios.']);
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    redirect_with('crear_usuario.php', ['error' => 'Correo inválido.']);
}
if ($contrasena !== $confirmar) {
    redirect_with('crear_usuario.php', ['error' => 'Las contraseñas no coinciden.']);
}
if (!in_array($estado, ['activo', 'bloqueado'], true)) {
    $estado = 'activo';
}
if (!in_array($rol, ['cliente', 'admin'], true)) {
    $rol = 'cliente';
}

// verificar correo único
$stmt = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1');
$stmt->bind_param('s', $correo);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->fetch_assoc()) {
    redirect_with('crear_usuario.php', ['error' => 'Ya existe un usuario con ese correo.']);
}

$hash = password_hash($contrasena, PASSWORD_DEFAULT);
$insert = $conexion->prepare('INSERT INTO usuarios (nombre, correo, contrasena, rol, codigo_2fa) VALUES (?, ?, ?, ?, NULL)');
$insert->bind_param('ssss', $nombre, $correo, $hash, $rol);
if ($insert->execute()) {
    // Intentamos establecer estado si columna existe
    $uid = $conexion->insert_id;
    $updateEstado = $conexion->prepare('UPDATE usuarios SET estado = ? WHERE id_usuario = ?');
    if ($updateEstado) {
        $updateEstado->bind_param('si', $estado, $uid);
        $updateEstado->execute();
    }
    redirect_with('admin_usuarios.php', ['mensaje' => 'Usuario creado correctamente.']);
} else {
    redirect_with('crear_usuario.php', ['error' => 'Error al crear el usuario.']);
}
