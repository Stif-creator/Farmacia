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
