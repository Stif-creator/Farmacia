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

$queryMarca = $conexion->prepare('SELECT nombre_marca FROM marcas WHERE id_marca = ? LIMIT 1');
$queryMarca->bind_param('i', $idMarca);
$queryMarca->execute();
$resultMarca = $queryMarca->get_result();
$marcaFila = $resultMarca->fetch_assoc();
if (!$marcaFila) {
    header('Location: crear_producto.php');
    exit;
}
$nombreMarca = $marcaFila['nombre_marca'];

$queryProveedor = $conexion->prepare('SELECT nombre FROM proveedores WHERE id_proveedor = ? LIMIT 1');
$queryProveedor->bind_param('i', $idProveedor);
$queryProveedor->execute();
$resultProveedor = $queryProveedor->get_result();
$proveedorFila = $resultProveedor->fetch_assoc();
if (!$proveedorFila) {
    header('Location: crear_producto.php');
    exit;
}

$imagenUrl = 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';
if (!empty($_FILES['imagen']['tmp_name'])) {
    $rutaUploads = 'uploads/';
    if (!is_dir($rutaUploads)) {
        mkdir($rutaUploads, 0755, true);
    }
    $nombreArchivo = time() . '_' . basename($_FILES['imagen']['name']);
    $destino = $rutaUploads . $nombreArchivo;
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
        $imagenUrl = $destino;
    }
}

$insert = $conexion->prepare('INSERT INTO productos (nombre, marca, descripcion, precio, stock, imagen, estado, id_categoria, id_marca, id_proveedor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$insert->bind_param('sssdissiii', $nombre, $nombreMarca, $descripcion, $precio, $stock, $imagenUrl, $estado, $idCategoria, $idMarca, $idProveedor);
$insert->execute();

header('Location: index.php');
exit;
