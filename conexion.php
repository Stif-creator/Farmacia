<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$host = 'localhost';
$usuario = 'root';
$password = '';
$baseDatos = 'farmacia_db';
$puerto = 3306;

$conexion = new mysqli($host, $usuario, $password, $baseDatos, $puerto);
$conexion->set_charset('utf8mb4');

if ($conexion->connect_error) {
    die('Error de conexión: ' . $conexion->connect_error);
}
