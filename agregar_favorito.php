<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];

if ($idProducto > 0) {
    $productoCheck = $conexion->prepare("SELECT id_producto FROM productos WHERE id_producto = ? AND estado = 'activo' LIMIT 1");
    $productoCheck->bind_param('i', $idProducto);
    $productoCheck->execute();
    if (!$productoCheck->get_result()->fetch_assoc()) {
        header('Location: index.php');
        exit;
    }

    $check = $conexion->prepare('SELECT id_favorito FROM favoritos WHERE id_usuario = ? AND id_producto = ? LIMIT 1');
    $check->bind_param('ii', $idUsuario, $idProducto);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $insert = $conexion->prepare('INSERT INTO favoritos (id_usuario, id_producto) VALUES (?, ?)');
        $insert->bind_param('ii', $idUsuario, $idProducto);
        $insert->execute();
    }
}
header('Location: favoritos.php');
exit;
