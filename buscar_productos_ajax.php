<?php
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$consulta = trim($_GET['q'] ?? '');
if (strlen($consulta) < 2) {
    echo json_encode([]);
    exit;
}

$termino = '%' . $consulta . '%';
$sql = "SELECT p.id_producto AS id, p.nombre, p.imagen, p.precio, COALESCE(c.nombre_categoria, '') AS categoria, COALESCE(m.nombre_marca, '') AS marca, COALESCE(pr.nombre, '') AS proveedor
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN marcas m ON p.id_marca = m.id_marca
    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
    WHERE p.estado = 'activo' AND (p.nombre LIKE ? OR p.marca LIKE ? OR c.nombre_categoria LIKE ? OR m.nombre_marca LIKE ? OR pr.nombre LIKE ?)
    ORDER BY p.nombre ASC
    LIMIT 8";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('sssss', $termino, $termino, $termino, $termino, $termino);
$stmt->execute();
$resultado = $stmt->get_result();

$items = [];
while ($fila = $resultado->fetch_assoc()) {
    $items[] = [
        'id' => intval($fila['id']),
        'nombre' => $fila['nombre'],
        'imagen' => $fila['imagen'] ?: 'https://placehold.co/120x120/16a34a/ffffff?text=Producto',
        'precio' => number_format((float) $fila['precio'], 2, '.', ''),
    ];
}

echo json_encode($items);
