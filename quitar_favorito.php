<?php
require_once 'auth.php';
soloCliente();
require_once 'conexion.php';

$idProducto = isset($_GET['id']) ? intval($_GET['id']) : 0;
$idUsuario = $_SESSION['id_usuario'];

if ($idProducto > 0) {
    $delete = $conexion->prepare('DELETE FROM favoritos WHERE id_usuario = ? AND id_producto = ?');
    $delete->bind_param('ii', $idUsuario, $idProducto);
    $delete->execute();
}
header('Location: favoritos.php');
exit;
