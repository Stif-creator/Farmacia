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

// Asegurar columnas de estado para soft delete sin romper relaciones.
$tablasConEstado = ['productos', 'categorias', 'marcas', 'proveedores'];
foreach ($tablasConEstado as $tablaEstado) {
    $estadoExiste = $conexion->query("SHOW COLUMNS FROM $tablaEstado LIKE 'estado'");
    if ($estadoExiste && $estadoExiste->num_rows === 0) {
        $conexion->query("ALTER TABLE $tablaEstado ADD COLUMN estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'");
    }
}

$conexion->query("CREATE TABLE IF NOT EXISTS producto_imagenes (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_producto_imagenes_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}
