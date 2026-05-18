<?php
require_once 'conexion.php';

$conexion->query("CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(200) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    codigo_2fa VARCHAR(6) DEFAULT NULL,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conexion->query("CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(150) NOT NULL UNIQUE,
    descripcion TEXT NOT NULL,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conexion->query("CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    marca VARCHAR(120) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) NOT NULL,
    estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    id_categoria INT NOT NULL,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conexion->query("CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado_venta VARCHAR(80) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conexion->query("CREATE TABLE IF NOT EXISTS detalle_venta (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conexion->query("CREATE TABLE IF NOT EXISTS favoritos (
    id_favorito INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_producto INT NOT NULL,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unico_favorito (id_usuario, id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$categorias = [
    ['Analgésicos', 'Medicamentos para aliviar el dolor y reducir la fiebre.'],
    ['Vitaminas', 'Suplementos para mantener tu energía y defensas.'],
    ['Cuidado personal', 'Productos de higiene y cuidado diario.'],
    ['Antigripales', 'Ayuda para síntomas de gripe y resfriado.'],
    ['Bebés', 'Artículos seguros para el cuidado infantil.'],
    ['Primeros auxilios', 'Productos indispensables para emergencias ligeras.']
];

$stmtCat = $conexion->prepare('INSERT IGNORE INTO categorias (nombre_categoria, descripcion) VALUES (?, ?)');
foreach ($categorias as $categoria) {
    $stmtCat->bind_param('ss', $categoria[0], $categoria[1]);
    $stmtCat->execute();
}

$hashAdmin = password_hash('Admin123', PASSWORD_DEFAULT);
$stmtAdmin = $conexion->prepare('INSERT IGNORE INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)');
$nombreAdmin = 'Administrador';
$correoAdmin = 'admin@farmacia.com';
$rolAdmin = 'admin';
$stmtAdmin->bind_param('ssss', $nombreAdmin, $correoAdmin, $hashAdmin, $rolAdmin);
$stmtAdmin->execute();

function obtenerCategoriaId($conexion, $nombre) {
    $query = $conexion->prepare('SELECT id_categoria FROM categorias WHERE nombre_categoria = ? LIMIT 1');
    $query->bind_param('s', $nombre);
    $query->execute();
    $resultado = $query->get_result();
    $fila = $resultado->fetch_assoc();
    return $fila['id_categoria'] ?? 0;
}

$productos = [
    ['Paracetamol', 'BioSalud', 'Analgésico para aliviar dolores leves y bajar la fiebre.', 4.50, 50, 'https://placehold.co/600x400/16a34a/ffffff?text=Paracetamol', 'activo', 'Analgésicos'],
    ['Ibuprofeno', 'FarmaciaPlus', 'Medicamento antiinflamatorio para el dolor y la fiebre.', 5.20, 40, 'https://placehold.co/600x400/16a34a/ffffff?text=Ibuprofeno', 'activo', 'Analgésicos'],
    ['Vitamina C', 'NutriVida', 'Suplemento de vitamina C para mejorar defensas y energía.', 8.90, 35, 'https://placehold.co/600x400/16a34a/ffffff?text=Vitamina+C', 'activo', 'Vitaminas'],
    ['Alcohol medicinal', 'SaludPlus', 'Alcohol 70% para desinfección y primeros auxilios.', 3.80, 60, 'https://placehold.co/600x400/16a34a/ffffff?text=Alcohol+medicinal', 'activo', 'Primeros auxilios'],
    ['Curitas', 'CuraRapida', 'Curitas adhesivas para pequeñas heridas y cortes.', 2.40, 80, 'https://placehold.co/600x400/16a34a/ffffff?text=Curitas', 'activo', 'Primeros auxilios'],
    ['Jarabe para la tos', 'RespiraBien', 'Jarabe expectorante para aliviar la tos seca y productiva.', 9.50, 25, 'https://placehold.co/600x400/16a34a/ffffff?text=Jarabe+tos', 'activo', 'Antigripales'],
    ['Protector solar', 'SolCare', 'Protector solar SPF50 para piel sensible.', 15.90, 20, 'https://placehold.co/600x400/16a34a/ffffff?text=Protector+solar', 'activo', 'Cuidado personal'],
    ['Pañales', 'BebéFresco', 'Pañales desechables para bebés con alta absorción.', 18.00, 30, 'https://placehold.co/600x400/16a34a/ffffff?text=Pañales', 'activo', 'Bebés'],
    ['Suero oral', 'HidraPlus', 'Solución de electrolitos para hidratación rápida.', 6.20, 45, 'https://placehold.co/600x400/16a34a/ffffff?text=Suero+oral', 'activo', 'Bebés'],
    ['Termómetro digital', 'MediTemp', 'Termómetro digital para medición rápida de temperatura.', 12.80, 15, 'https://placehold.co/600x400/16a34a/ffffff?text=Termómetro+digital', 'activo', 'Cuidado personal']
];

$stmtProd = $conexion->prepare('INSERT IGNORE INTO productos (nombre, marca, descripcion, precio, stock, imagen, estado, id_categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($productos as $producto) {
    $categoriaId = obtenerCategoriaId($conexion, $producto[7]);
    if ($categoriaId > 0) {
        $stmtProd->bind_param('sssdissi', $producto[0], $producto[1], $producto[2], $producto[3], $producto[4], $producto[5], $producto[6], $categoriaId);
        $stmtProd->execute();
    }
}

echo "Semilla ejecutada correctamente.\n";
echo "Admin: admin@farmacia.com / Admin123\n";
