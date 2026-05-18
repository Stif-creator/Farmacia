<?php
require_once 'auth.php';
soloAdmin();
require_once 'conexion.php';

$idProducto = intval($_POST['id_producto'] ?? 0);
$nombre = limpiarTexto($_POST['nombre'] ?? '');
$descripcion = limpiarTexto($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$idCategoria = intval($_POST['categoria'] ?? 0);
$idMarca = intval($_POST['marca'] ?? 0);
$idProveedor = intval($_POST['proveedor'] ?? 0);
$estado = $_POST['estado'] === 'inactivo' ? 'inactivo' : 'activo';

if ($idProducto <= 0 || $nombre === '' || $descripcion === '' || $precio <= 0 || $stock < 0 || $idCategoria <= 0 || $idMarca <= 0 || $idProveedor <= 0) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}

$query = $conexion->prepare('SELECT imagen FROM productos WHERE id_producto = ? LIMIT 1');
$query->bind_param('i', $idProducto);
$query->execute();
$resultado = $query->get_result();
$producto = $resultado->fetch_assoc();

$imagenUrl = $producto['imagen'] ?? 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';
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

$queryMarca = $conexion->prepare('SELECT nombre_marca FROM marcas WHERE id_marca = ? LIMIT 1');
$queryMarca->bind_param('i', $idMarca);
$queryMarca->execute();
$resultMarca = $queryMarca->get_result();
$marcaFila = $resultMarca->fetch_assoc();
if (!$marcaFila) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}
$nombreMarca = $marcaFila['nombre_marca'];

$queryProveedor = $conexion->prepare('SELECT id_proveedor FROM proveedores WHERE id_proveedor = ? LIMIT 1');
$queryProveedor->bind_param('i', $idProveedor);
$queryProveedor->execute();
$resultProveedor = $queryProveedor->get_result();
if (!$resultProveedor->fetch_assoc()) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}

$update = $conexion->prepare('UPDATE productos SET nombre = ?, marca = ?, descripcion = ?, precio = ?, stock = ?, imagen = ?, estado = ?, id_categoria = ?, id_marca = ?, id_proveedor = ? WHERE id_producto = ?');
$update->bind_param('sssdissiiii', $nombre, $nombreMarca, $descripcion, $precio, $stock, $imagenUrl, $estado, $idCategoria, $idMarca, $idProveedor, $idProducto);
$update->execute();

header('Location: index.php');
exit;
