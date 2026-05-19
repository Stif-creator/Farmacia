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
$imagenesEliminar = $_POST['eliminar_imagenes'] ?? [];
if (!is_array($imagenesEliminar)) {
    $imagenesEliminar = [];
}
$imagenesEliminar = array_values(array_unique(array_filter(array_map('trim', $imagenesEliminar))));

if ($idProducto <= 0 || $nombre === '' || $descripcion === '' || $precio <= 0 || $stock < 0 || $idCategoria <= 0 || $idMarca <= 0 || $idProveedor <= 0) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}

$query = $conexion->prepare('SELECT imagen, id_categoria, id_marca, id_proveedor FROM productos WHERE id_producto = ? LIMIT 1');
$query->bind_param('i', $idProducto);
$query->execute();
$resultado = $query->get_result();
$producto = $resultado->fetch_assoc();
if (!$producto) {
    header('Location: index.php');
    exit;
}

$queryCategoria = $conexion->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = ? AND (estado = 'activo' OR id_categoria = ?) LIMIT 1");
$queryCategoria->bind_param('ii', $idCategoria, $producto['id_categoria']);
$queryCategoria->execute();
if (!$queryCategoria->get_result()->fetch_assoc()) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}

$deleteImagen = $conexion->prepare('DELETE FROM producto_imagenes WHERE id_producto = ? AND ruta = ?');
foreach ($imagenesEliminar as $rutaEliminar) {
    $deleteImagen->bind_param('is', $idProducto, $rutaEliminar);
    $deleteImagen->execute();
}

$imagenesSubidas = subirImagenesProducto('imagenes');
if (empty($imagenesSubidas)) {
    $imagenesSubidas = subirImagenesProducto('imagen');
}
$imagenUrl = $producto['imagen'] ?? 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';
if (!empty($imagenesSubidas)) {
    $imagenUrl = $imagenesSubidas[0];
} elseif (in_array($imagenUrl, $imagenesEliminar, true)) {
    $siguienteImagen = $conexion->prepare('SELECT ruta FROM producto_imagenes WHERE id_producto = ? ORDER BY orden ASC, id_imagen ASC LIMIT 1');
    $siguienteImagen->bind_param('i', $idProducto);
    $siguienteImagen->execute();
    $imagenFila = $siguienteImagen->get_result()->fetch_assoc();
    $imagenUrl = $imagenFila['ruta'] ?? 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia';
}

$queryMarca = $conexion->prepare("SELECT nombre_marca FROM marcas WHERE id_marca = ? AND (estado = 'activo' OR id_marca = ?) LIMIT 1");
$queryMarca->bind_param('ii', $idMarca, $producto['id_marca']);
$queryMarca->execute();
$resultMarca = $queryMarca->get_result();
$marcaFila = $resultMarca->fetch_assoc();
if (!$marcaFila) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}
$nombreMarca = $marcaFila['nombre_marca'];

$queryProveedor = $conexion->prepare("SELECT id_proveedor FROM proveedores WHERE id_proveedor = ? AND (estado = 'activo' OR id_proveedor = ?) LIMIT 1");
$queryProveedor->bind_param('ii', $idProveedor, $producto['id_proveedor']);
$queryProveedor->execute();
$resultProveedor = $queryProveedor->get_result();
if (!$resultProveedor->fetch_assoc()) {
    header('Location: editar_producto.php?id=' . $idProducto);
    exit;
}

$update = $conexion->prepare('UPDATE productos SET nombre = ?, marca = ?, descripcion = ?, precio = ?, stock = ?, imagen = ?, estado = ?, id_categoria = ?, id_marca = ?, id_proveedor = ? WHERE id_producto = ?');
$update->bind_param('sssdissiiii', $nombre, $nombreMarca, $descripcion, $precio, $stock, $imagenUrl, $estado, $idCategoria, $idMarca, $idProveedor, $idProducto);
$update->execute();
registrarImagenesProducto($conexion, $idProducto, $imagenesSubidas);

header('Location: index.php');
exit;
