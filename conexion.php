<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = 'localhost';
$usuario = 'root';
$password = '';
$baseDatos = 'farmacia_db';
$puerto = 3306;

$conexion = new mysqli($host, $usuario, $password, $baseDatos, $puerto);
$conexion->set_charset('utf8mb4');

// Asegurar columna de estado de usuario para bloqueo/desbloqueo.
$estadoExiste = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'estado'");
if ($estadoExiste && $estadoExiste->num_rows === 0) {
    $conexion->query("ALTER TABLE usuarios ADD COLUMN estado ENUM('activo','bloqueado') NOT NULL DEFAULT 'activo'");
}

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}
