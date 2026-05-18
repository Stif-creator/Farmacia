<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $delete = $conexion->prepare('DELETE FROM categorias WHERE id_categoria = ?');
    $delete->bind_param('i', $id);
    $delete->execute();
}
header('Location: categorias.php');
exit;
