<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';
require_once 'auth.php';

$nombre = limpiarTexto($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

if ($nombre === '' || $correo === '' || $contrasena === '' || $confirmar === '') {
    header('Location: crear_admin.php?error=' . urlencode('Todos los campos son obligatorios.'));
    exit;
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header('Location: crear_admin.php?error=' . urlencode('Ingresa un correo válido.'));
    exit;
}
if (esAdminHardcodeado($correo)) {
    header('Location: crear_admin.php?error=' . urlencode('El correo admin@farmacia.com está reservado.'));
    exit;
}
if ($contrasena !== $confirmar) {
    header('Location: crear_admin.php?error=' . urlencode('Las contraseñas no coinciden.'));
    exit;
}

$verificacion = $conexion->prepare('SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1');
$verificacion->bind_param('s', $correo);
$verificacion->execute();
$existe = $verificacion->get_result()->fetch_assoc();
if ($existe) {
    header('Location: crear_admin.php?error=' . urlencode('El correo ya está registrado.'));
    exit;
}

$hash = password_hash($contrasena, PASSWORD_DEFAULT);
$rol = 'admin';
$estado = 'activo';
$insert = $conexion->prepare('INSERT INTO usuarios (nombre, correo, contrasena, rol, estado) VALUES (?, ?, ?, ?, ?)');
$insert->bind_param('sssss', $nombre, $correo, $hash, $rol, $estado);
$insert->execute();

header('Location: admin_usuarios.php?mensaje=' . urlencode('Administrador creado con éxito.'));
exit;
