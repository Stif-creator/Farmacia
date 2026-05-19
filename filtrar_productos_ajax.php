<?php
session_start();
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$categoria = intval($_GET['categoria'] ?? 0);
$consulta = trim($_GET['q'] ?? '');

$condiciones = ["p.estado = 'activo'"];
$parametros = [];
$tipos = '';

if ($categoria > 0) {
    $condiciones[] = 'p.id_categoria = ?';
    $parametros[] = $categoria;
    $tipos .= 'i';
}

if (strlen($consulta) >= 2) {
    $termino = '%' . $consulta . '%';
    $condiciones[] = '(p.nombre LIKE ? OR p.descripcion LIKE ? OR m.nombre_marca LIKE ? OR c.nombre_categoria LIKE ? OR pr.nombre LIKE ?)';
    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
    $parametros[] = $termino;
    $tipos .= 'sssss';
}

$sql = "SELECT p.*, COALESCE(c.nombre_categoria, '') AS nombre_categoria, COALESCE(m.nombre_marca, '') AS nombre_marca, COALESCE(pr.nombre, '') AS nombre_proveedor
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN marcas m ON p.id_marca = m.id_marca
    LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
    WHERE " . implode(' AND ', $condiciones) . "
    ORDER BY p.nombre ASC";

$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    echo json_encode(['html' => '<div class="col-12"><div class="alert alert-danger">Error al preparar la consulta.</div></div>', 'total' => 0]);
    exit;
}

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();

$html = '';
$total = $resultado->num_rows;
if ($total === 0) {
    $html = '<div class="col-12"><div class="alert alert-warning">No se encontraron productos con esos filtros.</div></div>';
} else {
    while ($producto = $resultado->fetch_assoc()) {
        $stock = intval($producto['stock']);
        $stockClass = $stock === 0 ? 'bg-danger text-white' : ($stock <= 10 ? 'bg-warning text-dark' : 'bg-success text-white');
        $stockLabel = $stock === 0 ? 'Sin stock' : ($stock <= 10 ? 'Poco stock' : 'Stock alto');
        $imagen = htmlspecialchars($producto['imagen'] ?: 'https://placehold.co/600x400/16a34a/ffffff?text=Producto+Farmacia');
        $nombre = htmlspecialchars($producto['nombre']);
        $marca = htmlspecialchars($producto['nombre_marca'] ?: $producto['marca']);
        $proveedor = htmlspecialchars($producto['nombre_proveedor']);
        $descripcion = htmlspecialchars(substr($producto['descripcion'], 0, 85));
        $precio = number_format((float) $producto['precio'], 2, ',', '.');
        $estadoClase = $producto['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo';
        $html .= '<div class="col-md-6 col-lg-4 producto-item">'
            . '<div class="card card-producto overflow-hidden h-100 hover-card">'
            . '<img src="' . $imagen . '" alt="' . $nombre . '">'
            . '<div class="card-body d-flex flex-column">'
            . '<span class="badge ' . $estadoClase . ' mb-2">' . htmlspecialchars(ucfirst($producto['estado'])) . '</span>'
            . '<span class="badge ' . $stockClass . ' mb-2">' . $stockLabel . '</span>'
            . '<h5 class="card-title">' . $nombre . '</h5>'
            . '<p class="texto-pequeno text-secondary mb-2">Marca ' . $marca . '</p>';
        if (!empty($proveedor)) {
            $html .= '<p class="texto-pequeno text-secondary mb-2">Proveedor ' . $proveedor . '</p>';
        }
        $html .= '<p class="card-text mb-3">' . $descripcion . '...</p>'
            . '<p class="fw-bold mb-3">$ ' . $precio . '</p>'
            . '<div class="mt-auto d-grid gap-2">'
            . '<a href="detalle_producto.php?id=' . intval($producto['id_producto']) . '" class="btn btn-outline-farmacia"><i class="bi bi-eye me-1"></i>Ver detalle</a>';
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'cliente') {
            $html .= '<a href="agregar_carrito.php?id=' . intval($producto['id_producto']) . '" class="btn btn-farmacia"><i class="bi bi-cart-plus me-1"></i>Agregar al carrito</a>'
                . '<a href="agregar_favorito.php?id=' . intval($producto['id_producto']) . '" class="btn btn-outline-farmacia"><i class="bi bi-heart me-1"></i>Favorito</a>';
        }
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
            $html .= '<div class="d-flex gap-2">'
                . '<a href="editar_producto.php?id=' . intval($producto['id_producto']) . '" class="btn btn-sm btn-secondary flex-fill"><i class="bi bi-pencil me-1"></i>Editar</a>'
                . '<a href="eliminar_producto.php?id=' . intval($producto['id_producto']) . '" class="btn btn-sm btn-danger flex-fill" onclick="return confirm(\'Deseas eliminar este producto?\');"><i class="bi bi-trash me-1"></i>Eliminar</a>'
                . '</div>';
        }
        $html .= '</div></div></div></div>';
    }
}

echo json_encode(['html' => $html, 'total' => $total]);
