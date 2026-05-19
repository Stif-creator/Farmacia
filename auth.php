<?php
session_start();
require_once 'conexion.php';

define('HARDCODED_ADMIN_EMAIL', 'admin@farmacia.com');
define('HARDCODED_ADMIN_NAME', 'Administrador Principal');
define('HARDCODED_ADMIN_CONFIG', __DIR__ . '/hardcoded_admin.json');

function cargarConfigAdminHardcodeado() {
    if (!file_exists(HARDCODED_ADMIN_CONFIG)) {
        $config = [
            'nombre' => HARDCODED_ADMIN_NAME,
            'password' => password_hash('Admin123', PASSWORD_DEFAULT),
        ];
        file_put_contents(HARDCODED_ADMIN_CONFIG, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $config;
    }

    $contenido = file_get_contents(HARDCODED_ADMIN_CONFIG);
    $config = json_decode($contenido, true);
    if (!is_array($config) || empty($config['password'])) {
        $config = [
            'nombre' => HARDCODED_ADMIN_NAME,
            'password' => password_hash('Admin123', PASSWORD_DEFAULT),
        ];
        file_put_contents(HARDCODED_ADMIN_CONFIG, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    return $config;
}

function obtenerPasswordAdminHardcodeado() {
    $config = cargarConfigAdminHardcodeado();
    return $config['password'];
}

function obtenerNombreAdminHardcodeado() {
    $config = cargarConfigAdminHardcodeado();
    return $config['nombre'] ?: HARDCODED_ADMIN_NAME;
}

function guardarAdminHardcodeado($nombre, $passwordHash = null) {
    $config = cargarConfigAdminHardcodeado();
    $config['nombre'] = $nombre ?: HARDCODED_ADMIN_NAME;
    if ($passwordHash !== null) {
        $config['password'] = $passwordHash;
    }
    file_put_contents(HARDCODED_ADMIN_CONFIG, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function esAdminHardcodeado($correo) {
    return strtolower(trim($correo)) === strtolower(HARDCODED_ADMIN_EMAIL);
}

function normalizarEstadoVenta($estado) {
    $estado = trim(strtolower((string) $estado));
    if ($estado === 'completada') {
        return 'realizada';
    }
    if (!in_array($estado, ['pendiente', 'realizada', 'cancelada'], true)) {
        return 'realizada';
    }
    return $estado;
}

function mostrarTextoEstadoVenta($estado) {
    return ucfirst(normalizarEstadoVenta($estado));
}

function obtenerClaseEstadoVenta($estado) {
    switch (normalizarEstadoVenta($estado)) {
        case 'pendiente':
            return 'badge-pendiente';
        case 'cancelada':
            return 'badge-cancelada';
        default:
            return 'badge-realizada';
    }
}

function actualizarEstadosVentas($conexion) {
    $limiteRealizada = date('Y-m-d H:i:s', time() - 30);
    $estadoPendiente = 'pendiente';
    $estadoRealizada = 'realizada';

    $stmt = $conexion->prepare("UPDATE ventas SET estado_venta = ? WHERE LOWER(TRIM(estado_venta)) = ? AND fecha <= ?");
    $stmt->bind_param('sss', $estadoRealizada, $estadoPendiente, $limiteRealizada);
    $stmt->execute();
}

function protegerRuta() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

function soloAdmin() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function soloCliente() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
        header('Location: index.php');
        exit;
    }
}

function limpiarTexto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

function subirImagenesProducto($campo = 'imagenes') {
    if (empty($_FILES[$campo])) {
        return [];
    }

    $archivos = $_FILES[$campo];
    $rutas = [];
    $rutaUploads = 'uploads/';
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!is_dir($rutaUploads)) {
        mkdir($rutaUploads, 0755, true);
    }

    $nombres = is_array($archivos['name']) ? $archivos['name'] : [$archivos['name']];
    $tmpNames = is_array($archivos['tmp_name']) ? $archivos['tmp_name'] : [$archivos['tmp_name']];
    $errores = is_array($archivos['error']) ? $archivos['error'] : [$archivos['error']];

    foreach ($nombres as $index => $nombreOriginal) {
        $tmpName = $tmpNames[$index] ?? '';
        $error = $errores[$index] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK || $tmpName === '') {
            continue;
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionesPermitidas, true)) {
            continue;
        }

        $nombreArchivo = uniqid('producto_', true) . '.' . $extension;
        $destino = $rutaUploads . $nombreArchivo;
        if (move_uploaded_file($tmpName, $destino)) {
            $rutas[] = $destino;
        }
    }

    return $rutas;
}

function registrarImagenesProducto($conexion, $idProducto, array $rutas) {
    if ($idProducto <= 0 || empty($rutas)) {
        return;
    }

    $ordenQuery = $conexion->prepare('SELECT COALESCE(MAX(orden), -1) + 1 AS siguiente FROM producto_imagenes WHERE id_producto = ?');
    $ordenQuery->bind_param('i', $idProducto);
    $ordenQuery->execute();
    $orden = intval($ordenQuery->get_result()->fetch_assoc()['siguiente'] ?? 0);

    $insert = $conexion->prepare('INSERT INTO producto_imagenes (id_producto, ruta, orden) VALUES (?, ?, ?)');
    foreach ($rutas as $ruta) {
        $insert->bind_param('isi', $idProducto, $ruta, $orden);
        $insert->execute();
        $orden++;
    }
}
