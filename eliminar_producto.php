<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $query = $conexion->prepare('SELECT imagen FROM productos WHERE id_producto = ? LIMIT 1');
    $query->bind_param('i', $id);
    $query->execute();
    $resultado = $query->get_result();
    $producto = $resultado->fetch_assoc();

    if ($producto) {
        if (!empty($producto['imagen']) && strpos($producto['imagen'], 'uploads/') === 0) {
            @unlink($producto['imagen']);
        }
        $delete = $conexion->prepare('DELETE FROM productos WHERE id_producto = ?');
        $delete->bind_param('i', $id);
        $delete->execute();
    }
}
header('Location: index.php');
exit;
