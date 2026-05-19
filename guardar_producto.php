<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$nombre = limpiarTexto($_POST['nombre'] ?? '');
$descripcion = limpiarTexto($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$idCategoria = intval($_POST['categoria'] ?? 0);
$idMarca = intval($_POST['marca'] ?? 0);
$idProveedor = intval($_POST['proveedor'] ?? 0);
$estado = $_POST['estado'] === 'inactivo' ? 'inactivo' : 'activo';

if ($nombre === '' || $descripcion === '' || $precio <= 0 || $stock < 0 || $idCategoria <= 0 || $idMarca <= 0 || $idProveedor <= 0) {
    header('Location: crear_producto.php');
    exit;
}

$queryCategoria = $conexion->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ? AND estado = 'activo' LIMIT 1");
$queryCategoria->bind_param('i', $idCategoria);
$queryCategoria->execute();
if (!$queryCategoria->get_result()->fetch_assoc()) {
    header('Location: crear_producto.php');
    exit;
}

$queryMarca = $conexion->prepare("SELECT nombre_marca FROM marcas WHERE id_marca = ? AND estado = 'activo' LIMIT 1");
$queryMarca->bind_param('i', $idMarca);
$queryMarca->execute();
$resultMarca = $queryMarca->get_result();
$marcaFila = $resultMarca->fetch_assoc();
if (!$marcaFila) {
    header('Location: crear_producto.php');
    exit;
}
$nombreMarca = $marcaFila['nombre_marca'];

$queryProveedor = $conexion->prepare("SELECT nombre FROM proveedores WHERE id_proveedor = ? AND estado = 'activo' LIMIT 1");
$queryProveedor->bind_param('i', $idProveedor);
$queryProveedor->execute();
$resultProveedor = $queryProveedor->get_result();
$proveedorFila = $resultProveedor->fetch_assoc();
if (!$proveedorFila) {
    header('Location: crear_producto.php');
    exit;
}

$imagenesSubidas = subirImagenesProducto('imagenes');
if (empty($imagenesSubidas)) {
    $imagenesSubidas = subirImagenesProducto('imagen');
}
$imagenUrl = $imagenesSubidas[0] ?? 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';

$insert = $conexion->prepare('INSERT INTO productos (nombre, marca, descripcion, precio, stock, imagen, estado, id_categoria, id_marca, id_proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$insert->bind_param('sssdissiii', $nombre, $nombreMarca, $descripcion, $precio, $stock, $imagenUrl, $estado, $idCategoria, $idMarca, $idProveedor);
$insert->execute();
$idProducto = $conexion->insert_id;
registrarImagenesProducto($conexion, $idProducto, $imagenesSubidas);

header('Location: index.php');
exit;
